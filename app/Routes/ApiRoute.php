<?php

namespace App\Routes;

use App\Helpers\RendersRoute;

/**
 * The paths of the json api, every one under one prefix.
 *
 * A case is built from that prefix rather than repeating it, and is one path rather
 * than one endpoint: the verbs bound to it share it. This is also the key every
 * published operation is filed under, and the document is held to describing every
 * case — so a case is what makes an endpoint documented and validated, and an api
 * path declared on one of the page indexes escapes both without complaint. It is
 * the one place an api path may be written out, which is why scaffolding starts here.
 */
enum ApiRoute: string
{
    use RendersRoute;

    public const string prefix = '/api';

    case authenticated = self::prefix.'/authenticated';
    case cache = self::prefix.'/cache';
    case cache_key = self::prefix.'/cache/{key}';
    case cache_locks = self::prefix.'/cache-locks';
    case cache_locks_key = self::prefix.'/cache-locks/{key}';
    #[AdminLink]
    case readme = self::prefix.'/readme';
    case user = self::prefix.'/user';
    case user_token = self::prefix.'/user/tokens/{token}';
    case user_tokens = self::prefix.'/user/tokens';
}
