<?php

namespace App\Routes;

use App\Helpers\IsSingleton;

class ApiRoutes
{
    use IsSingleton;

    public const prefix = 'api';

    public string $discovery = self::prefix;

    public string $authenticated = self::prefix.'/authenticated';
    public string $login = self::prefix.'/login';
    public string $logout = self::prefix.'/logout';
}