<?php

use LaravelSqlProfiler\SqlProfiler;
use LaravelSqlProfiler\Models\SqlQueryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Pulisci la tabella esistente
    SqlQueryLog::truncate();

    // Crea la tabella di test
    Schema::create('test_users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_users');
    SqlQueryLog::truncate();
});

test('it can be instantiated', function () {
    $profiler = new SqlProfiler(app());
    expect($profiler)->toBeInstanceOf(SqlProfiler::class);
});

test('it starts profiling when enabled', function () {
    config(['sqlprofiler.enabled' => true]);
    
    $profiler = new SqlProfiler(app());
    $profiler->start();
    
    // Esegui una query
    DB::table('test_users')->insert([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);
    
    // Forza il salvataggio delle query differite (per i test)
    $profiler->flushQueries();
    
    // Verifica che la query sia stata salvata
    expect(SqlQueryLog::count())->toBeGreaterThan(0);
    
    $log = SqlQueryLog::first();
    expect($log->sql)->toContain('insert');
    expect($log->sql)->toContain('test_users');
});

test('it does not log when disabled', function () {
    config(['sqlprofiler.enabled' => false]);
    
    $profiler = new SqlProfiler(app());
    $profiler->start();
    
    // Esegui una query
    DB::table('test_users')->insert([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);
    
    // Forza il salvataggio (non dovrebbe salvare nulla)
    $profiler->flushQueries();
    
    // Verifica che nessuna query sia stata salvata
    expect(SqlQueryLog::count())->toBe(0);
});

test('it can be configured', function () {
    $profiler = new SqlProfiler(app());
    
    $stats = $profiler->configure([
        'max_queries' => 200,
        'min_execution_time' => 5
    ])->getStats();
    
    expect($stats['max_queries'])->toBe(200);
    expect($stats['min_execution_time'])->toBe(5);
});
