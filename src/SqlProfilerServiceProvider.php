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

                Log::channel(config('sqlprofiler.log_channel', 'stack'))
                    ->debug("[SQL Profiler] {$sql} | bindings: {$bindings} | time: {$time} ms");
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
    }
}
