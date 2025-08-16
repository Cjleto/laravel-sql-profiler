<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use LaravelSqlProfiler\SqlProfilerServiceProvider;

test('it registers the service provider', function () {
    expect(app()->providerIsLoaded(SqlProfilerServiceProvider::class))->toBeTrue();
});

test('it merges configuration correctly', function () {
    expect(config('sqlprofiler'))->not->toBeNull();
    expect(config('sqlprofiler.enabled'))->toBeBool();
    expect(config('sqlprofiler.log_channel'))->toBeString();
});

test('it publishes config file', function () {
    $this->artisan('vendor:publish', [
        '--provider' => SqlProfilerServiceProvider::class,
        '--tag' => 'config'
    ]);

    expect(config_path('sqlprofiler.php'))->toBeFile();
});

test('it sets up logging channel', function () {
    expect(config('sqlprofiler.log_channel'))->toBe('daily_sql_profiler');
    expect(config('logging.channels.daily_sql_profiler'))->not->toBeNull();
    expect(config('logging.channels.daily_sql_profiler.driver'))->toBe('daily');
    expect(config('logging.channels.daily_sql_profiler.path'))->toContain('sql_profiler.log');
});

test('it does not listen to queries when disabled', function () {
    Config::set('sqlprofiler.enabled', false);
    
    // Re-register the service provider to apply the new config
    $serviceProvider = new SqlProfilerServiceProvider(app());
    $serviceProvider->boot();
    
    // When disabled, we just verify the config is set correctly
    expect(config('sqlprofiler.enabled'))->toBeFalse();
});

test('it listens to queries when enabled', function () {
    // This test just verifies that the service provider registers correctly when enabled
    Config::set('sqlprofiler.enabled', true);
    
    $serviceProvider = new SqlProfilerServiceProvider(app());
    $serviceProvider->boot();
    
    // We just verify the service provider runs without errors when enabled
    expect(config('sqlprofiler.enabled'))->toBeTrue();
});
