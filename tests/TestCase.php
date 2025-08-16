<?php

namespace LaravelSqlProfiler\Tests;

use LaravelSqlProfiler\SqlProfilerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SqlProfilerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup logging for tests
        $app['config']->set('logging.default', 'single');
        $app['config']->set('logging.channels.single', [
            'driver' => 'single',
            'path' => storage_path('logs/test.log'),
            'level' => 'debug',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        
        // Create a simple test table
        $this->artisan('migrate', ['--database' => 'testbench']);
        
        \Illuminate\Support\Facades\Schema::create('test_table', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }
}
