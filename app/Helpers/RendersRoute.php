<?php

namespace App\Helpers;

use Illuminate\Http\Request;

trait RendersRoute
{
    /** @param  array<string, string|int>  $route */
    public function isActive(Request $Request, array $route = []): bool
    {
        return $Request->is(ltrim(self::render($this->value, $route), '/').'*');
    }

    /** @param  array<string, string|int>  $route */
    public function isExact(Request $Request, array $route = []): bool
    {
        return $Request->path() === ltrim(self::render($this->value, $route), '/');
    }

    /**
     * The case's path with its placeholders filled in.
     *
     * A templated case is still the only place the path is spelled, so callers
     * that need a concrete url — a test, a link — ask for it here rather than
     * rebuilding the string beside the enum.
     *
     * @param  array<string, string|int>  $route
     */
    public function url(array $route = []): string
    {
        return self::render($this->value, $route);
    }

    /** @param  array<string, string|int>  $route */
    private static function render(string $url, array $route = []): string
    {
        foreach ($route as $search => $replace) {
            $url = str_replace("{{$search}}", (string) $replace, $url);
        }

        return $url;
    }
}
