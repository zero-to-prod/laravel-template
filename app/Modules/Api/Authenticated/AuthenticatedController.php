<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Models\Authorized;
use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class AuthenticatedController
{
    #[ApiSchema(AuthenticatedSchema::schema)]
    public function __invoke(): JsonResponse
    {
        if (! auth('sanctum')->check()) {
            return api_response()->unauthorized();
        }

        return api_response()->ok(Authorized::from());
    }
}
