<?php

namespace App\Routes;

use App\Helpers\IsSingleton;

class Api
{
    use IsSingleton;

    public const prefix = 'api';

    public string $authenticated = self::prefix.'/authenticated';
    public string $login = self::prefix.'/login';
    public string $logout = self::prefix.'/logout';
}