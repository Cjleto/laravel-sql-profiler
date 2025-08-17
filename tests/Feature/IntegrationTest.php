<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelSqlProfiler\Models\SqlQueryLog;
use LaravelSqlProfiler\SqlProfiler;

beforeEach(function () {
    // Pulisci i dati del database
    SqlQueryLog::truncate();
    
    // Configura il profiler per i test
    Config::set('sqlprofiler.enabled', true);
    
    // Create test table
    Schema::create('integration_test_table', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    });
    
    // Ottieni il profiler per i test
    $this->profiler = app(SqlProfiler::class);
    $this->profiler->start();
});

afterEach(function () {
    Schema::dropIfExists('integration_test_table');
    SqlQueryLog::truncate();
});

test('it actually saves queries to database', function () {
    // Execute a query
    DB::table('integration_test_table')->insert([
        'name' => 'Integration Test',
        'email' => 'integration@test.com'
    ]);

    // Forza il salvataggio per i test
    $this->profiler->flushQueries();

    // Check if query was saved to database
    expect(SqlQueryLog::count())->toBeGreaterThan(0);
    
    $log = SqlQueryLog::first();
    expect($log->sql)->toContain('integration_test_table');
    expect($log->sql)->toContain('insert');
    expect($log->bindings)->toContain('Integration Test');
    expect($log->time)->toBeGreaterThan(0);
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

    // Forza il salvataggio per i test
    $this->profiler->flushQueries();

    // Should have logged the insert queries
    expect(SqlQueryLog::count())->toBeGreaterThanOrEqual(2);
    
    $logs = SqlQueryLog::all();
    $bindings = $logs->pluck('bindings')->implode(' ');
    expect($bindings)->toContain('User 1');
    expect($bindings)->toContain('User 2');
});

test('it respects enabled disabled setting', function () {
    // This test verifies that the configuration can be changed
    Config::set('sqlprofiler.enabled', false);
    
    expect(config('sqlprofiler.enabled'))->toBeFalse();
    
    // Create new profiler with disabled config
    $disabledProfiler = new SqlProfiler(app());
    $disabledProfiler->start();
    
    DB::table('integration_test_table')->insert([
        'name' => 'Config Test',
        'email' => 'config@test.com'
    ]);

    $disabledProfiler->flushQueries();

    // Should not log when disabled
    expect(SqlQueryLog::count())->toBe(0);
});

test('it handles queries with special characters', function () {
    // Execute query with special characters
    DB::table('integration_test_table')->insert([
        'name' => "O'Connor & Smith",
        'email' => 'test+email@domain.com'
    ]);

    $this->profiler->flushQueries();

    $log = SqlQueryLog::first();
    expect($log->bindings)->toContain('test+email@domain.com');
    expect($log->bindings)->toContain("O'Connor & Smith");
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

    $this->profiler->flushQueries();

    $logs = SqlQueryLog::all();
    $bindings = $logs->pluck('bindings')->implode(' ');
    expect($bindings)->toContain('Transaction User 1');
    expect($bindings)->toContain('Transaction User 2');
});
