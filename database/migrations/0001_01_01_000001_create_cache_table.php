<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', static function (Blueprint $Blueprint) {
            $Blueprint->string('key')->primary();
            $Blueprint->mediumText('value');
            $Blueprint->integer('expiration');
        });

        Schema::create('cache_locks', static function (Blueprint $Blueprint) {
            $Blueprint->string('key')->primary();
            $Blueprint->string('owner');
            $Blueprint->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
