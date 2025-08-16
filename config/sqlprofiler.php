<?php

return [
    // Attiva o disattiva il profiler
    'enabled' => env('SQL_PROFILER_ENABLED', false),

    // Canale di log (es. stack, daily, single)
    'log_channel' => env('SQL_PROFILER_CHANNEL', 'daily_sql_profiler'),

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
