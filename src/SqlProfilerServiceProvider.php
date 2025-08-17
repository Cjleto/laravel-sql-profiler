<?php

namespace LaravelSqlProfiler;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SqlProfilerServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // Pubblica le view del pacchetto
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sqlprofiler'),
        ], 'views');

        // Carica le rotte del pacchetto
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Carica le views del pacchetto
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sqlprofiler');

        // Registra il middleware per la dashboard
        $this->app['router']->aliasMiddleware('sqlprofiler.dashboard_access', \LaravelSqlProfiler\Http\Middleware\SqlDashboardAccess::class);

        // Carica sempre la migration del pacchetto
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Permetti la pubblicazione della migration
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Avvia il profiling SQL
        $this->app->make(SqlProfiler::class)->start();

        // Pubblica il file di configurazione
        $this->publishes([
            __DIR__ . '/../config/sqlprofiler.php' => config_path('sqlprofiler.php'),
        ], 'config');

        // Pubblica le view del pacchetto
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sqlprofiler'),
        ], 'views');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sqlprofiler.php',
            'sqlprofiler'
        );

        // Registra la classe SqlProfiler nel container
        $this->app->singleton(SqlProfiler::class, function ($app) {
            return new SqlProfiler($app);
        });

        // Merge manuale nel logging globale
        $this->app->make('config')->set(
            'logging.channels.daily_sql_profiler',
            config('sqlprofiler.logging.channels.daily_sql_profiler')
        );
    }
}
