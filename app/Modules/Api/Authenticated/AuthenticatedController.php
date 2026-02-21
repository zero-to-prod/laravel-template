<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Endpoint;
use App\Modules\Api\ErrorCode;
use App\Modules\Api\Models\Authorized;
use Illuminate\Http\JsonResponse;

#[Endpoint(
    description: 'Check if the current token is valid.',
    errors: [ErrorCode::unauthorized],
    response: Authorized::class,
)]
class AuthenticatedController
{
    public function __invoke(): JsonResponse
    {
        if (! auth('sanctum')->check()) {
            return api_response()->unauthorized();
        }

        return api_response()->ok(Authorized::from());
    }
}