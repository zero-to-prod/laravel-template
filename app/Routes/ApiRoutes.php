<?php

namespace App\Routes;

use App\Helpers\IsSingleton;

class ApiRoutes
{
    use IsSingleton;

    public const string prefix = 'api';

    public string $docs = self::prefix.'/docs';
    public string $authenticated = self::prefix.'/authenticated';
    public string $login = self::prefix.'/login';
    public string $logout = self::prefix.'/logout';
}
