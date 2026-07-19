<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $Blueprint) {
            $Blueprint->ulid('id')->primary();
            $Blueprint->string('name');
            $Blueprint->string('email')->unique();
            $Blueprint->timestamp('email_verified_at')->nullable();
            $Blueprint->string('password');
            $Blueprint->rememberToken();
            $Blueprint->timestamps();
        });

        Schema::create('password_reset_tokens', static function (Blueprint $Blueprint) {
            $Blueprint->string('email')->primary();
            $Blueprint->string('token');
            $Blueprint->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', static function (Blueprint $Blueprint) {
            $Blueprint->string('id')->primary();
            $Blueprint->foreignUlid('user_id')->nullable()->index();
            $Blueprint->string('ip_address', 45)->nullable();
            $Blueprint->text('user_agent')->nullable();
            $Blueprint->longText('payload');
            $Blueprint->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
