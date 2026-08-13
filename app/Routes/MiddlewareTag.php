<?php

namespace App\Routes;

/**
 * The middleware this application attaches, named once.
 *
 * A value is the string middleware is actually registered and requested under,
 * which is not always the case name — so route groups, page patterns and role
 * guards cannot disagree about a name, and an alias cannot drift from its use.
 * It names guards, not paths, so it is deliberately not one of the route indexes.
 */
enum MiddlewareTag: string
{
    case web = 'web';
    case api = 'api';
    case auth = 'auth';
    case verified = 'verified';
    case sanctum = 'auth:sanctum';
    case role = 'role';
}
