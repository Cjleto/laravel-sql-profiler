# Laravel SQL Profiler

Un piccolo package per loggare e profilare le query SQL eseguite da Laravel.

## Installazione

```bash
composer require tuo-username/laravel-sql-profiler


php artisan vendor:publish --tag=config --provider="LaravelSqlProfiler\SqlProfilerServiceProvider"