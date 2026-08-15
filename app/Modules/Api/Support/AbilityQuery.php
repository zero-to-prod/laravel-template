<?php

namespace App\Modules\Api\Support;

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Routes\MiddlewareTag;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class AbilityQuery
{
    /** @return array<string, list<HttpVerb>> */
    public static function get(): array
    {
        $endpoints = [];

        foreach (self::groups() as $group) {
            $endpoints = [...$endpoints, ...$group];
        }

        return $endpoints;
    }

    /** @return array<string, array<string, list<HttpVerb>>> */
    public static function groups(): array
    {
        $bound = self::bound();
        $groups = [];

        /** @var array<string, array{route_index?: mixed, credential_role?: mixed}> $schemas */
        $schemas = Config::array('openapi.schemas', []);

        foreach ($schemas as $name => $configuration) {
            $role = $configuration['credential_role'] ?? null;

            if (is_string($role) && ! self::userHolds($role)) {
                continue;
            }

            $routes = $configuration['route_index'] ?? null;
            $endpoints = [];

            if (! is_string($routes) || ! enum_exists($routes) || ! is_subclass_of($routes, BackedEnum::class)) {
                continue;
            }

            foreach ($routes::cases() as $Route) {
                if (! is_string($Route->value)) {
                    continue;
                }

                $verbs = array_values(array_filter(
                    HttpVerb::cases(),
                    static fn (HttpVerb $Verb): bool => in_array($Verb->ability($Route->value), $bound, true),
                ));

                if ($verbs !== []) {
                    $endpoints[$Route->value] = $verbs;
                }
            }

            $groups[$name] = $endpoints;
        }

        return $groups;
    }

    private static function userHolds(string $role): bool
    {
        $User = Auth::user();

        return $User instanceof User && $User->hasRole($role);
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
