<?php

namespace LaravelSqlProfiler\Http\Middleware;

use Closure;

class SqlDashboardAccess
{
    public function handle($request, Closure $next)
    {
        // Di default accesso sempre consentito, ma puoi aggiungere logica custom qui
        return $next($request);
    }
}
