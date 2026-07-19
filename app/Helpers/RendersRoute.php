<?php

namespace App\Helpers;

use Illuminate\Http\Request;

trait RendersRoute
{
    public function isActive(Request $request, array $route = []): bool
    {
        return $request->is(ltrim(self::render($this->value, $route), '/').'*');
    }

    public function isExact(Request $request, array $route = []): bool
    {
        return $request->path() === ltrim(self::render($this->value, $route), '/');
    }

    private static function render(
        string $url,
        array $route = [],
        array|object $query = [],
        string $numeric_prefix = '',
        ?string $arg_separator = null,
        int $encoding_type = 1
    ): string {
        foreach ($route as $search => $replace) {
            $url = str_replace("{{$search}}", $replace, $url);
        }

        return $query
            ? $url.'?'.http_build_query($query, $numeric_prefix, $arg_separator, $encoding_type)
            : $url;
    }
}
