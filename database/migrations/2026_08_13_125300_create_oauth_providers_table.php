<?php

use App\Modules\Login\GoogleUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_providers', static function (Blueprint $Blueprint) {
            $Blueprint->foreignUlid('user_id')->comment('The user the OAuth identity belongs to');
            $Blueprint->string(GoogleUser::sub)->unique()->comment('The provider subject identifier');
            $Blueprint->string(GoogleUser::name)->comment('The name supplied by the provider');
            $Blueprint->string(GoogleUser::given_name)->comment('The given name supplied by the provider');
            $Blueprint->string(GoogleUser::family_name)->comment('The family name supplied by the provider');
            $Blueprint->text(GoogleUser::picture)->comment('The profile picture URL supplied by the provider');
            $Blueprint->string(GoogleUser::email)->comment('The email supplied by the provider');
            $Blueprint->boolean(GoogleUser::email_verified)->comment('Whether the provider verified the email');
            $Blueprint->string(GoogleUser::hd)->nullable()->comment('The hosted domain supplied by the provider');
            $Blueprint->string(GoogleUser::id)->comment('The compatibility identifier supplied by Socialite');
            $Blueprint->boolean(GoogleUser::verified_email)->comment('The compatibility email verification flag supplied by Socialite');
            $Blueprint->text(GoogleUser::link)->nullable()->comment('The profile URL supplied by Socialite');

            $Blueprint->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_providers');
    }
};
