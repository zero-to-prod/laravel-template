<?php

namespace App\Routes;

use App\Helpers\RendersRoute;

enum ApiRoute: string
{
    use RendersRoute;

    public const string prefix = '/api';

    case authenticated = self::prefix.'/authenticated';
    case login = self::prefix.'/login';
    case logout = self::prefix.'/logout';
}
