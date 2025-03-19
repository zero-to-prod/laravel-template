<?php

namespace App\Routes;

use App\Helpers\IsSingleton;

class RouteManager
{
    use IsSingleton;

    public function home(): string
    {
        return web()->home;
    }

    public function login(): string
    {
        return web()->login;
    }

    public function logout(): string
    {
        return web()->logout;
    }

    public function register(): string
    {
        return web()->register;
    }
}