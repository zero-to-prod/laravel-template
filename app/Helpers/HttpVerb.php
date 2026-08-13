<?php

namespace App\Helpers;

use Illuminate\Http\Request;

/**
 * The request methods a token can be granted, and how one ability is spelled.
 *
 * A case is a column of the grant grid and the first half of an ability string; the
 * path is the second, so an ability is one verb reaching one path and nothing wider.
 * Both the screen that grants and the guard that enforces build the string here, so
 * they cannot disagree about its shape — and if they did, the guard would silently
 * refuse everything the screen had just granted. A method that is not one of these
 * reads rather than writes, so it is held to the ability that reads.
 */
enum HttpVerb: string
{
    public const string every = '*';
    public const string separator = ':';

    case get = 'GET';
    case post = 'POST';
    case put = 'PUT';
    case patch = 'PATCH';
    case delete = 'DELETE';

    public static function of(Request $Request): self
    {
        return self::tryFrom($Request->getMethod()) ?? self::get;
    }

    public function ability(string $path): string
    {
        return $this->value.self::separator.'/'.ltrim($path, '/');
    }
}
