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
    case readme = self::prefix.'/readme';
    case user = self::prefix.'/user';
    case user_token = self::prefix.'/user/tokens/{token}';
    case user_tokens = self::prefix.'/user/tokens';
}
