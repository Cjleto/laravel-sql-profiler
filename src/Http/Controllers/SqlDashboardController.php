<?php

namespace LaravelSqlProfiler\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use LaravelSqlProfiler\Models\SqlQueryLog;
use Illuminate\Http\Request;

class SqlDashboardController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'sqlprofiler_dashboard';
        $queries = Cache::remember($cacheKey, 10, function () {
            return SqlQueryLog::orderByDesc('time')->limit(100)->get();
        });

        // Rilevamento N+1: raggruppa per SQL e conta le occorrenze
        $nPlusOne = $queries->groupBy('sql')->filter(function ($group) {
            return $group->count() > 1;
        });

        return view('sqlprofiler::dashboard', [
            'queries' => $queries,
            'nPlusOne' => $nPlusOne,
        ]);
    }
}
