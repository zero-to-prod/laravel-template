<?php

namespace App\Mcp\Endpoint;

use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\PublicApiSchema;
use App\Routes\Admin;
use App\Routes\ApiRoute;

enum EndpointApi: string
{
    case public = 'public';
    case admin = 'admin';

    public function prefix(): string
    {
        return match ($this) {
            self::public => ApiRoute::prefix,
            self::admin => Admin::prefix.'/api',
        };
    }

    public function routePrefix(): string
    {
        return match ($this) {
            self::public => ApiRoute::prefix,
            self::admin => Admin::prefix,
        };
    }

    /** @return class-string */
    public function route(): string
    {
        return match ($this) {
            self::public => ApiRoute::class,
            self::admin => Admin::class,
        };
    }

    /** @return class-string */
    public function schemaAttribute(): string
    {
        return match ($this) {
            self::public => PublicApiSchema::class,
            self::admin => AdminApiSchema::class,
        };
    }

    public function routesFile(bool $authenticated): string
    {
        return match ($this) {
            self::public => $authenticated ? 'routes/api_auth.php' : 'routes/api.php',
            self::admin => 'routes/api_admin.php',
        };
    }
}
