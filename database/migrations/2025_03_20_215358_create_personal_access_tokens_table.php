<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', static function (Blueprint $Blueprint) {
            $Blueprint->ulid('id')->primary()->comment('The unique identifier of the token');
            $Blueprint->string('tokenable_type')->comment('The class of the model the token belongs to');
            $Blueprint->string('tokenable_id', 255)->comment('The identifier of the model the token belongs to');
            $Blueprint->index(['tokenable_id', 'tokenable_type']);
            $Blueprint->string('name')->comment('The name the token was issued under');
            $Blueprint->string('token', 64)->unique()->comment('The hashed token');
            $Blueprint->text('abilities')->nullable()->comment('The abilities granted to the token');
            $Blueprint->timestamp('last_used_at')->nullable()->comment('When the token was last used');
            $Blueprint->timestamp('expires_at')->nullable()->comment('When the token expires');
            $Blueprint->timestamp('created_at')->nullable()->comment('When the token was created');
            $Blueprint->timestamp('updated_at')->nullable()->comment('When the token was last updated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
