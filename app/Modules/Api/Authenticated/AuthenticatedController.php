<?php

namespace App\Modules\Api\Authenticated;

use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class AuthenticatedController
{
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
