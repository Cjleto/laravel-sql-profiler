<?php

return [
    // Attiva o disattiva il profiler
    'enabled' => env('SQL_PROFILER_ENABLED', false),

    // Canale di log (es. stack, daily, single)
    'log_channel' => env('SQL_PROFILER_CHANNEL', 'stack'),
];
