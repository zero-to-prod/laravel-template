<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', static function (Blueprint $Blueprint) {
            $Blueprint->id();
            $Blueprint->string('tokenable_type');
            $Blueprint->string('tokenable_id', 255);
            $Blueprint->index(['tokenable_id', 'tokenable_type']);
            $Blueprint->string('name');
            $Blueprint->string('token', 64)->unique();
            $Blueprint->text('abilities')->nullable();
            $Blueprint->timestamp('last_used_at')->nullable();
            $Blueprint->timestamp('expires_at')->nullable();
            $Blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
