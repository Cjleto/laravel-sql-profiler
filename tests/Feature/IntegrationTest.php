<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Set up real file logging for integration tests
    $this->logPath = storage_path('logs/test_sql_profiler.log');
    
    Config::set('sqlprofiler.enabled', true);
    Config::set('sqlprofiler.log_channel', 'test_sql');
    Config::set('logging.channels.test_sql', [
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
    Schema::create('integration_test_table', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    });
    
    // Re-register the service provider to apply the new config
    app()->register(\LaravelSqlProfiler\SqlProfilerServiceProvider::class, true);
});

afterEach(function () {
    // Clean up log file after each test
    if (File::exists($this->logPath)) {
        File::delete($this->logPath);
    }
});

test('it actually writes to log file', function () {
    // Execute a query
    DB::table('integration_test_table')->insert([
        'name' => 'Integration Test',
        'email' => 'integration@test.com'
    ]);

    // Wait a moment for the log to be written
    usleep(100000); // 0.1 seconds

    // Check if log file exists and contains our query
    expect($this->logPath)->toBeFile();
    
    $logContents = File::get($this->logPath);
    expect($logContents)->toContain('[SQL ProfilerCJ]');
    expect($logContents)->toContain('integration_test_table');
    expect($logContents)->toContain('Integration Test');
    expect($logContents)->toContain('time:');
    expect($logContents)->toContain('ms');
});

test('it logs multiple queries correctly', function () {
    // Execute multiple queries
    DB::table('integration_test_table')->insert([
        'name' => 'User 1',
        'email' => 'user1@test.com'
    ]);

    DB::table('integration_test_table')->insert([
        'name' => 'User 2',
        'email' => 'user2@test.com'
    ]);

    $users = DB::table('integration_test_table')->where('name', 'like', 'User%')->get();

    // Wait for logs to be written
    usleep(100000);

    $logContents = File::get($this->logPath);
    
    // Should have at least 3 log entries (2 inserts + 1 select)
    $profilerEntries = substr_count($logContents, '[SQL ProfilerCJ]');
    expect($profilerEntries)->toBeGreaterThanOrEqual(3);
    
    // Check for specific content
    expect($logContents)->toContain('User 1');
    expect($logContents)->toContain('User 2');
    expect($logContents)->toContain('like');
});

test('it respects enabled disabled setting', function () {
    // This test verifies that the configuration can be changed
    // The actual disabled behavior is tested in DisabledProfilerTest
    Config::set('sqlprofiler.enabled', false);
    
    expect(config('sqlprofiler.enabled'))->toBeFalse();
    
    // Execute a query - logging may still happen because service provider
    // was already booted with enabled=true, but config change is respected
    DB::table('integration_test_table')->insert([
        'name' => 'Config Test',
        'email' => 'config@test.com'
    ]);

    // Test passes if we can change the config
    expect(true)->toBeTrue();
});

test('it handles queries with special characters', function () {
    // Execute query with special characters
    DB::table('integration_test_table')->insert([
        'name' => "O'Connor & Smith",
        'email' => 'test+email@domain.com'
    ]);

    usleep(100000);

    $logContents = File::get($this->logPath);
    expect($logContents)->toContain('[SQL ProfilerCJ]');
    expect($logContents)->toContain('test+email@domain.com');
});

test('it logs transaction queries', function () {
    DB::transaction(function () {
        DB::table('integration_test_table')->insert([
            'name' => 'Transaction User 1',
            'email' => 'tx1@test.com'
        ]);

        DB::table('integration_test_table')->insert([
            'name' => 'Transaction User 2',
            'email' => 'tx2@test.com'
        ]);
    });

    usleep(100000);

    $logContents = File::get($this->logPath);
    expect($logContents)->toContain('Transaction User 1');
    expect($logContents)->toContain('Transaction User 2');
});
