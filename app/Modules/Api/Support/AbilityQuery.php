<?php

namespace App\Modules\Api\Support;

use App\Helpers\HttpVerb;
use App\Routes\ApiRoute;
use App\Routes\MiddlewareTag;
use Illuminate\Support\Facades\Route;

class AbilityQuery
{
    /** @return array<string, list<HttpVerb>> */
    public static function get(): array
    {
        $bound = self::bound();
        $endpoints = [];

        foreach (ApiRoute::cases() as $ApiRoute) {
            $verbs = array_values(array_filter(
                HttpVerb::cases(),
                static fn (HttpVerb $Verb): bool => in_array($Verb->ability($ApiRoute->value), $bound, true),
            ));

            if ($verbs !== []) {
                $endpoints[$ApiRoute->value] = $verbs;
            }
        }

        return $endpoints;
    }

    /** @return list<string> */
    public static function abilities(): array
    {
        $abilities = [];

        foreach (self::get() as $path => $verbs) {
            foreach ($verbs as $Verb) {
                $abilities[] = $Verb->ability($path);
            }
        }

        return $abilities;
    }

    /** @return list<string> */
    private static function bound(): array
    {
        $bound = [];

        foreach (Route::getRoutes()->getRoutes() as $BoundRoute) {
            if (! in_array(MiddlewareTag::sanctum->value, $BoundRoute->gatherMiddleware(), true)) {
                continue;
            }

            foreach ($BoundRoute->methods() as $method) {
                $Verb = HttpVerb::tryFrom($method);

                if ($Verb !== null) {
                    $bound[] = $Verb->ability($BoundRoute->uri());
                }
            }
        }

        return $bound;
    }
}
