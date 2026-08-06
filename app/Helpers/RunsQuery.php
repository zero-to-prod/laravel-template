<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Event;

trait RunsQuery
{
    public static function get(mixed ...$args): mixed
    {
        Event::dispatch(self::class);

        return new self()->handle(...$args);
    }
}
