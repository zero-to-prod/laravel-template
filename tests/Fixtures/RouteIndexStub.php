<?php

namespace Tests\Fixtures;

use App\Routes\AdminLink;

/**
 * A route enum the registry does not name, with one tagged case giving no order.
 *
 * Every real index is registered and every tagged case there gives an order, so this
 * is the only way to reach both branches: a tag outside the registry is read when
 * the enum is asked directly and absent from the pages that ask the registry, and an
 * order-less tag sorts last.
 */
enum RouteIndexStub: string
{
    #[AdminLink]
    case bare = '/bare';
    case unmarked = '/unmarked';
}
