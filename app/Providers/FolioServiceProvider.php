<?php

namespace App\Providers;

use App\Routes\MiddlewareTag;
use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Folio::path(resource_path('views/pages'))->middleware([
            'email/verify/*' => [MiddlewareTag::auth->value],
            '*' => [
                //
            ],
        ]);
    }
}
