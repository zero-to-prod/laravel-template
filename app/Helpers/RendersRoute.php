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
        return trim($Request->path(), '/') === trim(self::render($this->value, $route), '/');
    }

    /** @param  array<string, string|int>  $route */
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
