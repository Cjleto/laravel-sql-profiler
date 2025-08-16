<?php

use Illuminate\Support\Facades\Config;

test('it has default configuration values', function () {
    $config = config('sqlprofiler');
    
    expect($config['enabled'])->toBeFalse();
    expect($config['log_channel'])->toBe('daily_sql_profiler');
    expect($config)->toHaveKey('logging');
});

test('it respects environment variables', function () {
    Config::set('sqlprofiler.enabled', true);
    Config::set('sqlprofiler.log_channel', 'custom');
    
    expect(config('sqlprofiler.enabled'))->toBeTrue();
    expect(config('sqlprofiler.log_channel'))->toBe('custom');
});

test('it has correct logging channel configuration', function () {
    $loggingConfig = config('sqlprofiler.logging.channels.daily_sql_profiler');
    
    expect($loggingConfig['driver'])->toBe('daily');
    expect($loggingConfig['level'])->toBe('debug');
    expect($loggingConfig['days'])->toBe(14);
    expect($loggingConfig['path'])->toContain('sql_profiler.log');
});

test('it can override log path', function () {
    $customPath = '/custom/path/sql.log';
    Config::set('sqlprofiler.logging.channels.daily_sql_profiler.path', $customPath);
    
    $path = config('sqlprofiler.logging.channels.daily_sql_profiler.path');
    expect($path)->toBe($customPath);
});

test('it can change log level', function () {
    Config::set('sqlprofiler.logging.channels.daily_sql_profiler.level', 'info');
    
    $level = config('sqlprofiler.logging.channels.daily_sql_profiler.level');
    expect($level)->toBe('info');
});

test('it can change retention days', function () {
    Config::set('sqlprofiler.logging.channels.daily_sql_profiler.days', 30);
    
    $days = config('sqlprofiler.logging.channels.daily_sql_profiler.days');
    expect($days)->toBe(30);
});

test('it validates boolean enabled setting', function () {
    Config::set('sqlprofiler.enabled', 'true');
    expect(config('sqlprofiler.enabled'))->not->toBe(true);
    
    Config::set('sqlprofiler.enabled', true);
    expect(config('sqlprofiler.enabled'))->toBeTrue();
    
    Config::set('sqlprofiler.enabled', false);
    expect(config('sqlprofiler.enabled'))->toBeFalse();
});
