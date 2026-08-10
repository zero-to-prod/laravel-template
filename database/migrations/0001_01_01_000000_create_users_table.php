<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $Blueprint) {
            $Blueprint->ulid('id')->primary()->comment('The unique identifier of the user');
            $Blueprint->string('name')->comment('The users name');
            $Blueprint->string('email')->unique()->comment('The users email');
            $Blueprint->timestamp('email_verified_at')->nullable()->comment('When the users email was verified');
            $Blueprint->string('password')->comment('The users hashed password');
            $Blueprint->string('remember_token', 100)->nullable()->comment('The token that keeps the user signed in between sessions');
            $Blueprint->timestamp('created_at')->nullable()->comment('When the user was created');
            $Blueprint->timestamp('updated_at')->nullable()->comment('When the user was last updated');
        });

        Schema::create('password_reset_tokens', static function (Blueprint $Blueprint) {
            $Blueprint->string('email')->primary()->comment('The email the reset token was issued to');
            $Blueprint->string('token')->comment('The hashed password reset token');
            $Blueprint->timestamp('created_at')->nullable()->comment('When the reset token was issued');
        });

        Schema::create('sessions', static function (Blueprint $Blueprint) {
            $Blueprint->string('id')->primary()->comment('The session identifier');
            $Blueprint->foreignUlid('user_id')->nullable()->index()->comment('The user the session belongs to');
            $Blueprint->string('ip_address', 45)->nullable()->comment('The address the session was last seen from');
            $Blueprint->text('user_agent')->nullable()->comment('The user agent the session was last seen from');
            $Blueprint->longText('payload')->comment('The serialized session data');
            $Blueprint->integer('last_activity')->index()->comment('The unix timestamp of the last request on the session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
