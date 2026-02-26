<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

trait RunsQuery
{
    private static array $_cache = [];

    public static function get(...$args)
    {
        Event::dispatch(self::class);
        $return = (new self)->handle(...$args);

        return $return;
    }
}
