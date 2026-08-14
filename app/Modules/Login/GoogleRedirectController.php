<?php

namespace App\Modules\Login;

use App\Helpers\SocialiteDriver;
use Laravel\Socialite\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class GoogleRedirectController
{
    public function __invoke(): RedirectResponse
    {
        return Socialite::driver(SocialiteDriver::google->value)->redirect();
    }
}
