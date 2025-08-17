<?php

namespace LaravelSqlProfiler;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;

class SqlProfiler
{
    protected $app;
    protected $deferredQueries = [];
    protected $queryCount = 0;
    protected $maxQueries = 100;
    protected $minExecutionTime = 1; // ms

    protected $excludeTables = [
        'sql_query_logs', 'logs', 'jobs', 'failed_jobs', 
        'sessions', 'cache', 'migrations', 'permissions',
        'roles', 'model_has_roles', 'model_has_permissions'
    ];

    protected $excludePatterns = [
        'count(*) as aggregate',
        'exists (select',
        'where `guard_name`'
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->loadConfiguration();
    }

    /**
     * Carica la configurazione dal file config
     */
    protected function loadConfiguration(): void
    {
        $config = config('sqlprofiler.profiler', []);
        
        $this->maxQueries = $config['max_queries'] ?? 100;
        $this->minExecutionTime = $config['min_execution_time'] ?? 1;
        
        if (!empty($config['exclude_tables'])) {
            $this->excludeTables = array_merge($this->excludeTables, $config['exclude_tables']);
        }
        
        if (!empty($config['exclude_patterns'])) {
            $this->excludePatterns = array_merge($this->excludePatterns, $config['exclude_patterns']);
        }
    }

    /**
     * Avvia il profiling SQL
     */
    public function start(): void
    {
        if (!$this->shouldProfile()) {
            return;
        }

        $this->registerQueryListener();
        $this->registerTerminatingCallback();
    }

    /**
     * Determina se il profiling deve essere attivo
     */
    protected function shouldProfile(): bool
    {
        return config('sqlprofiler.enabled', false) && 
               (!$this->app->runningInConsole() || $this->app->runningUnitTests());
    }

    /**
     * Registra il listener per le query SQL
     */
    protected function registerQueryListener(): void
    {
        DB::listen(function ($query) {
            $this->handleQuery($query);
        });
    }

    /**
     * Gestisce una singola query SQL
     */
    protected function handleQuery($query): void
    {
        $this->queryCount++;

        // Limita il numero massimo di query per richiesta
        if ($this->queryCount > $this->maxQueries) {
            return;
        }

        $sql = $query->sql;

        // Escludi query su tabelle di sistema
        if ($this->shouldExcludeQuery($sql)) {
            return;
        }

        // Salva solo query importanti
        if ($this->isImportantQuery($sql, $query->time)) {
            $this->collectQuery($query);
        }
    }

    /**
     * Determina se una query deve essere esclusa
     */
    protected function shouldExcludeQuery(string $sql): bool
    {
        // Controllo tabelle escluse
        foreach ($this->excludeTables as $table) {
            if (stripos($sql, $table) !== false) {
                return true;
            }
        }

        // Controllo pattern esclusi
        foreach ($this->excludePatterns as $pattern) {
            if (stripos($sql, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina se una query è importante e deve essere loggata
     */
    protected function isImportantQuery(string $sql, float $time): bool
    {
        // Sempre logga INSERT, UPDATE, DELETE
        if (preg_match('/^(insert|update|delete)/i', $sql)) {
            return true;
        }

        // Per le SELECT, logga solo quelle importanti
        if (stripos($sql, 'select') === 0) {
            return !stripos($sql, 'count(*)') && 
                   !stripos($sql, 'exists') && 
                   $time > $this->minExecutionTime;
        }

        return false;
    }

    /**
     * Raccoglie i dati della query per il salvataggio differito
     */
    protected function collectQuery($query): void
    {
        $this->deferredQueries[] = [
            'sql' => $query->sql,
            'bindings' => json_encode($query->bindings),
            'time' => $query->time,
            'connection' => $query->connectionName ?? null,
            'user_id' => Auth::check() ? Auth::id() : null,
        ];
    }

    /**
     * Registra il callback per salvare le query dopo la response
     */
    protected function registerTerminatingCallback(): void
    {
        $this->app->terminating(function () {
            $this->saveDeferredQueries();
        });
    }

    /**
     * Salva tutte le query raccolte nel database
     */
    protected function saveDeferredQueries(): void
    {
        if (empty($this->deferredQueries)) {
            return;
        }

        try {
            // Disabilita temporaneamente il query log per evitare loop
            DB::flushQueryLog();

            foreach ($this->deferredQueries as $queryData) {
                Models\SqlQueryLog::create($queryData);
            }
        } catch (\Exception $e) {
            // Ignora errori per evitare crash dell'applicazione
        }
    }

    /**
     * Forza il salvataggio delle query (utile per i test)
     */
    public function flushQueries(): void
    {
        $this->saveDeferredQueries();
        $this->deferredQueries = [];
    }

    /**
     * Ottieni le query differite (utile per debug)
     */
    public function getDeferredQueries(): array
    {
        return $this->deferredQueries;
    }

    /**
     * Configura i parametri del profiler
     */
    public function configure(array $options = []): self
    {
        if (isset($options['max_queries'])) {
            $this->maxQueries = $options['max_queries'];
        }

        if (isset($options['min_execution_time'])) {
            $this->minExecutionTime = $options['min_execution_time'];
        }

        if (isset($options['exclude_tables'])) {
            $this->excludeTables = array_merge($this->excludeTables, $options['exclude_tables']);
        }

        if (isset($options['exclude_patterns'])) {
            $this->excludePatterns = array_merge($this->excludePatterns, $options['exclude_patterns']);
        }

        return $this;
    }

    /**
     * Ottieni statistiche del profiler
     */
    public function getStats(): array
    {
        return [
            'total_queries' => $this->queryCount,
            'deferred_queries' => count($this->deferredQueries),
            'max_queries' => $this->maxQueries,
            'min_execution_time' => $this->minExecutionTime,
        ];
    }
}
