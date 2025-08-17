<?php

return [
    // Attiva o disattiva il profiler
    'enabled' => env('SQL_PROFILER_ENABLED', false),

    // Canale di log (es. stack, daily, single)
    'log_channel' => env('SQL_PROFILER_CHANNEL', 'daily_sql_profiler'),

    // Configurazioni del profiler
    'profiler' => [
        // Numero massimo di query per richiesta
        'max_queries' => env('SQL_PROFILER_MAX_QUERIES', 100),
        
        // Tempo minimo di esecuzione per loggare le SELECT (ms)
        'min_execution_time' => env('SQL_PROFILER_MIN_TIME', 1),
        
        // Tabelle aggiuntive da escludere
        'exclude_tables' => [],
        
        // Pattern aggiuntivi da escludere
        'exclude_patterns' => [],
    ],

    'logging' => [
        'channels' => [
            'daily_sql_profiler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/sql_profiler.log'),
                'level' => 'debug',
                'days' => 14,
            ],
        ],
    ],
];
