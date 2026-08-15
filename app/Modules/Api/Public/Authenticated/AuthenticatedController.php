<?php

namespace App\Modules\Api\Public\Authenticated;

use App\Modules\Api\Support\PublicApiSchema;
use Illuminate\Http\JsonResponse;

readonly class AuthenticatedController
{
    #[PublicApiSchema(static function (): array {
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
