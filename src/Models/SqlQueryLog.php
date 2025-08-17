<?php

namespace LaravelSqlProfiler\Models;

use Illuminate\Database\Eloquent\Model;

class SqlQueryLog extends Model
{
    protected $table = 'sql_query_logs';
    protected $fillable = [
        'sql', 'bindings', 'time', 'connection', 'user_id'
    ];
}
