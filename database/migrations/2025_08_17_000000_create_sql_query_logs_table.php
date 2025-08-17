<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sql_query_logs', function (Blueprint $table) {
            $table->id();
            $table->text('sql');
            $table->text('bindings')->nullable();
            $table->float('time')->nullable();
            $table->string('connection')->nullable();
            $table->string('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sql_query_logs');
    }
};
