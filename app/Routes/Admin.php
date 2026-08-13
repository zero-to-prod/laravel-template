<?php

namespace App\Routes;

use App\Helpers\RendersRoute;

/**
 * The role-gated pages, every one under one prefix.
 *
 * A case is built from that prefix rather than repeating it, because the guard is
 * attached to the pattern and not to each route: a path that falls outside it is
 * served with no authentication and no role check. There is no sitemap here, so
 * none of these is ever advertised. These pages carry their own navigation, which
 * stands in for the default rail anywhere under the prefix.
 */
enum Admin: string
{
    use RendersRoute;

    public const string prefix = '/admin';

    case index = self::prefix;
    case links = self::prefix.'/links';
}
