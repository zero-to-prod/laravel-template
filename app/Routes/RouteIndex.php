<?php

namespace App\Routes;

use BackedEnum;

/**
 * The route indexes this application serves, one case per enum.
 *
 * A case here is the whole of registering an index, and a single query answers what
 * is registered: anything reading the routes this application serves asks that
 * rather than naming the enums itself. The registry is the declaration, so
 * answering touches no filesystem and loads nothing to do it — and the order these
 * cases are declared in is the order the indexes are read in. An enum this does not
 * name is not one of this application's routes, wherever it lives.
 */
enum RouteIndex: string
{
    case admin = Admin::class;
    case api = ApiRoute::class;
    case auth = Auth::class;
    case web = Web::class;

    /** @return class-string<BackedEnum> */
    public function enum(): string
    {
        /** @var class-string<BackedEnum> */
        return $this->value;
    }
}
