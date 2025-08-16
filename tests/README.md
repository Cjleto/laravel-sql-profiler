# Tests Documentation

## Panoramica

Questo package include una suite completa di test per garantire il corretto funzionamento del Laravel SQL Profiler.

## Tipi di Test

### 1. **TestCase.php** - Base Class
Classe base per tutti i test che:
- Configura l'ambiente di test con Orchestra Testbench
- Imposta un database SQLite in memoria per i test
- Configura il logging per l'ambiente di test
- Crea tabelle di test per simulare query reali

### 2. **SqlProfilerServiceProviderTest.php** - Test del Service Provider
Testa il service provider del package:
- ✅ Registrazione corretta del service provider
- ✅ Merge delle configurazioni
- ✅ Pubblicazione del file di configurazione
- ✅ Setup del canale di logging
- ✅ Attivazione/disattivazione del listening delle query

### 3. **SqlLoggerTest.php** - Test del Logging SQL
Testa la funzionalità principale di logging:
- ✅ Logging delle query SELECT
- ✅ Logging delle query INSERT con bindings
- ✅ Logging delle query UPDATE con bindings
- ✅ Logging delle query DELETE
- ✅ Inclusione del tempo di esecuzione nei log
- ✅ Gestione di query complesse con JOIN
- ✅ Comportamento quando il profiler è disabilitato
- ✅ Utilizzo di canali di log personalizzati

### 4. **ConfigurationTest.php** - Test della Configurazione
Testa tutte le opzioni di configurazione:
- ✅ Valori di configurazione di default
- ✅ Rispetto delle variabili d'ambiente
- ✅ Configurazione del canale di logging
- ✅ Override del percorso del log
- ✅ Modifica del livello di log
- ✅ Configurazione dei giorni di retention
- ✅ Validazione delle impostazioni booleane

### 5. **IntegrationTest.php** - Test di Integrazione
Testa il funzionamento end-to-end:
- ✅ Scrittura effettiva nei file di log
- ✅ Logging di multiple query correttamente
- ✅ Rispetto delle impostazioni enabled/disabled
- ✅ Gestione di caratteri speciali nelle query
- ✅ Logging delle query in transazioni

## Eseguire i Test

### Installazione delle dipendenze
```bash
composer install
```

### Eseguire tutti i test
```bash
composer test
# oppure
vendor/bin/phpunit
```

### Eseguire test specifici
```bash
# Test del service provider
vendor/bin/phpunit tests/SqlProfilerServiceProviderTest.php

# Test di configurazione
vendor/bin/phpunit tests/ConfigurationTest.php

# Test di integrazione
vendor/bin/phpunit tests/IntegrationTest.php
```

### Generare report di copertura
```bash
composer test-coverage
```

### Analisi statica del codice
```bash
composer analyse
```

## Struttura dei Test

```
tests/
├── TestCase.php                     # Classe base per tutti i test
├── SqlProfilerServiceProviderTest.php # Test del service provider
├── SqlLoggerTest.php                # Test del logging SQL
├── ConfigurationTest.php            # Test della configurazione
└── IntegrationTest.php              # Test di integrazione end-to-end
```

## Mock e Fake

I test utilizzano:
- **Mockery** per fare il mock delle facade di Laravel (Log, Config)
- **Orchestra Testbench** per simulare l'ambiente Laravel
- **SQLite in memoria** per i test del database
- **File temporanei** per i test di integrazione

## Continuous Integration

I test sono progettati per essere eseguiti in ambienti CI/CD. Assicurati di:

1. Installare le dipendenze: `composer install`
2. Eseguire i test: `composer test`
3. Verificare la copertura: `composer test-coverage`
4. Analisi statica: `composer analyse`

## Debugging dei Test

Per il debugging dei test:

```bash
# Eseguire con output verbose
vendor/bin/phpunit --verbose

# Eseguire un singolo test
vendor/bin/phpunit --filter=test_method_name

# Debug con stop on failure
vendor/bin/phpunit --stop-on-failure
```

## Note Importanti

- I test di integrazione creano file di log temporanei che vengono automaticamente puliti
- I test utilizzano un database SQLite in memoria per prestazioni ottimali
- Le configurazioni di test sono isolate dall'ambiente di sviluppo
- I mock garantiscono che i test siano veloci e affidabili
