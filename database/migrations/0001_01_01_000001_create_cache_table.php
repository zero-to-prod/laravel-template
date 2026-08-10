<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', static function (Blueprint $Blueprint) {
            $Blueprint->string('key')->primary()->comment('The cache key');
            $Blueprint->mediumText('value')->comment('The serialized cached value');
            $Blueprint->integer('expiration')->comment('The unix timestamp the entry expires at');
        });

        Schema::create('cache_locks', static function (Blueprint $Blueprint) {
            $Blueprint->string('key')->primary()->comment('The name of the lock');
            $Blueprint->string('owner')->comment('The identifier of the process holding the lock');
            $Blueprint->integer('expiration')->comment('The unix timestamp the lock expires at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
