# Laravel SQL Profiler

Un package per Laravel che consente di loggare e profilare tutte le query SQL eseguite dall'applicazione. Perfetto per il debugging e l'ottimizzazione delle performance.

## Caratteristiche

- ✅ Logging automatico di tutte le query SQL
- ✅ Registrazione dei binding parameters
- ✅ Misurazione del tempo di esecuzione
- ✅ Canale di log dedicato e configurabile
- ✅ Attivazione/disattivazione tramite variabile d'ambiente
- ✅ Compatibile con Laravel 10.x e 11.x

## Requisiti

- PHP 8.1 o superiore
- Laravel 10.x o 11.x

## Installazione

1. Installa il package tramite Composer:

```bash
composer require cjleto/laravel-sql-profiler
```

2. Pubblica il file di configurazione:

```bash
php artisan vendor:publish --tag=config --provider="LaravelSqlProfiler\SqlProfilerServiceProvider"
```

## Configurazione

Dopo aver pubblicato il file di configurazione, troverai il file `config/sqlprofiler.php` con le seguenti opzioni:

```php
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
```

### Variabili d'ambiente

Aggiungi le seguenti variabili al tuo file `.env`:

```env
# Attiva il profiler SQL (true/false)
SQL_PROFILER_ENABLED=true

# Canale di log personalizzato (opzionale)
SQL_PROFILER_CHANNEL=daily_sql_profiler
```

## Utilizzo

Una volta configurato e attivato, il package inizierà automaticamente a loggare tutte le query SQL. Non è necessario alcun codice aggiuntivo.

### Esempio di output nel log

```
[2024-08-16 10:30:45] local.DEBUG: [SQL ProfilerCJ] select * from users where email = ? | bindings: ["user@example.com"] | time: 1.23 ms
[2024-08-16 10:30:46] local.DEBUG: [SQL ProfilerCJ] insert into posts (title, content, user_id) values (?, ?, ?) | bindings: ["Titolo Post","Contenuto del post",1] | time: 2.45 ms
```

### Posizione del file di log

Per impostazione predefinita, i log vengono salvati in:
```
storage/logs/sql_profiler.log
```

### Disattivazione

Per disattivare il profiler, imposta la variabile d'ambiente:

```env
SQL_PROFILER_ENABLED=false
```

oppure rimuovi completamente la variabile dal file `.env`.

## Personalizzazione

### Canale di log personalizzato

Puoi utilizzare qualsiasi canale di log configurato in `config/logging.php`:

```env
SQL_PROFILER_CHANNEL=stack
```

### Configurazione avanzata

Modifica il file `config/sqlprofiler.php` per personalizzare ulteriormente il comportamento:

- Cambia il driver di log (daily, single, stack, etc.)
- Modifica il percorso del file di log
- Imposta il livello di log
- Configura la rotazione dei file (per il driver daily)

## Sviluppo e Testing

### Ambiente di sviluppo

Generalmente, vorrai attivare il profiler solo in ambiente di sviluppo:

```env
# Solo in sviluppo
SQL_PROFILER_ENABLED=true
```

### Ambiente di produzione

⚠️ **Attenzione**: Usa con cautela in produzione poiché il logging di tutte le query può impattare sulle performance e generare file di log molto grandi.

## Autore

- **Francesco Leto** - [letociccio@gmail.com](mailto:letociccio@gmail.com)

## Licenza

Questo package è distribuito sotto licenza MIT. Vedi il file [LICENSE](LICENSE) per maggiori dettagli.

## Contributi

I contributi sono benvenuti! Sentiti libero di aprire issue o pull request.

## Changelog

### v1.0.0
- Prima release
- Supporto per Laravel 10.x e 11.x
- Logging automatico delle query SQL
- Configurazione tramite file e variabili d'ambiente