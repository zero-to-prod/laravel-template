<?php

namespace App\Modules\Api\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class SchemaController
{
    public function __invoke(string $schema, Router $Router): JsonResponse
    {
        /** @var array{attribute?: string, openapi?: array<string, mixed>} $configuration */
        $configuration = Config::array("openapi.schemas.$schema", []);
        $attribute = $configuration['attribute'] ?? null;

        if ($attribute === null || ! is_a($attribute, ApiSchema::class, true)) {
            throw new RuntimeException("OpenAPI schema [$schema] is not configured.");
        }

        return new JsonResponse((new SchemaGenerator(
            $Router,
            $attribute,
            $configuration['openapi'] ?? [],
        ))->document());
    }
}
