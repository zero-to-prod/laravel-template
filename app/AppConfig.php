<?php

namespace App;

use App\Routes\RouteIndex;
use BackedEnum;

final class AppConfig
{
    /** @return list<class-string<BackedEnum>> */
    public static function routeIndexes(): array
    {
        return array_map(
            static fn (RouteIndex $Index): string => $Index->enum(),
            RouteIndex::cases(),
        );
    }
}
