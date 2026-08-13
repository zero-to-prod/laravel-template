<?php

namespace App\Helpers;

use App\Routes\MiddlewareTag;

/**
 * The roles this application grants, and the source of truth for each name.
 *
 * A registered user holds none: a role is granted only by the migration that
 * creates it, to the account named in the configuration. Each case builds the guard
 * string routes are protected with, so the name cannot drift from what protects
 * them — and because that string is assembled by concatenation, a value must be
 * safe to appear as a middleware parameter. A new case is inert until a row for it
 * exists under the default guard, which makes the migration part of adding one.
 */
enum Role: string
{
    case admin = 'admin';

    public function middleware(): string
    {
        return MiddlewareTag::role->value.':'.$this->value;
    }
}
