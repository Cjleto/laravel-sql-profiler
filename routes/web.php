<?php

use Illuminate\Support\Facades\Route;
use LaravelSqlProfiler\Http\Controllers\SqlDashboardController;

Route::middleware(['web', 'sqlprofiler.dashboard_access'])->group(function () {
    Route::get('/sql-dashboard', [SqlDashboardController::class, 'index'])->name('sqlprofiler.dashboard');
});
