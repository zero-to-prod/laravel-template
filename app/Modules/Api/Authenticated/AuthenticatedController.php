<?php

namespace App\Modules\Api\Authenticated;

use Illuminate\Http\JsonResponse;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class AuthenticatedController
{
    /** @throws ReflectionException */
    #[ApiSchema(static function (): array {
        return AuthenticatedSchema::schema();
    })]
    public function __invoke(): JsonResponse
    {
        if (! auth('sanctum')->check()) {
            return api_response()->unauthorized();
        }

        return api_response()->ok(AuthenticatedResponse::from());
    }
}
