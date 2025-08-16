<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Set up with DISABLED profiler from the start
    $this->logPath = storage_path('logs/disabled_test.log');
    
    Config::set('sqlprofiler.enabled', false); // DISABLED!
    Config::set('sqlprofiler.log_channel', 'disabled_test');
    Config::set('logging.channels.disabled_test', [
        'driver' => 'single',
        'path' => $this->logPath,
        'level' => 'debug',
    ]);
    
    // Ensure directory exists
    if (!File::exists(dirname($this->logPath))) {
        File::makeDirectory(dirname($this->logPath), 0755, true);
    }
    
    // Clean up any existing log file
    if (File::exists($this->logPath)) {
        File::delete($this->logPath);
    }
    
    // Create test table
    Schema::create('disabled_test_table', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    
    // Register the service provider with disabled config
    app()->register(\LaravelSqlProfiler\SqlProfilerServiceProvider::class, true);
});

afterEach(function () {
    if (File::exists($this->logPath)) {
        File::delete($this->logPath);
    }
});

test('it does not log when profiler is disabled', function () {
    // Execute queries
    DB::table('disabled_test_table')->insert(['name' => 'Should Not Log']);
    DB::table('disabled_test_table')->select('*')->get();
    
    usleep(100000); // Wait for any potential logging
    
    // Log file should not exist since profiler is disabled
    expect($this->logPath)->not->toBeFile();
});

test('it respects disabled configuration', function () {
    expect(config('sqlprofiler.enabled'))->toBeFalse();
    
    // Execute a query
    DB::table('disabled_test_table')->insert(['name' => 'Test Entry']);
    
    usleep(50000);
    
    // No log file should be created
    expect($this->logPath)->not->toBeFile();
});
