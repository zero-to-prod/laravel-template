<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        View::addLocation(dirname(__DIR__).'/View/Components');

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
