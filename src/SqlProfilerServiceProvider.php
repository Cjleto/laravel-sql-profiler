<?php

namespace LaravelSqlProfiler;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SqlProfilerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (config('sqlprofiler.enabled', false)) {

            DB::listen(function ($query) {
                $sql = $query->sql;
                $bindings = json_encode($query->bindings);
                $time = $query->time;

                Log::channel(config('sqlprofiler.log_channel', 'daily_sql_profiler'))
                    ->debug("[SQL ProfilerCJ] {$sql} | bindings: {$bindings} | time: {$time} ms");
            });
        }

        // Pubblica il file di configurazione
        $this->publishes([
            __DIR__ . '/../config/sqlprofiler.php' => config_path('sqlprofiler.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sqlprofiler.php',
            'sqlprofiler'
        );

        // Merge manuale nel logging globale
        $this->app->make('config')->set(
            'logging.channels.daily_sql_profiler',
            config('sqlprofiler.logging.channels.daily_sql_profiler')
        );

    }
}
