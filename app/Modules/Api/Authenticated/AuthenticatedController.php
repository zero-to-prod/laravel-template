<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Models\Authorized;
use App\Modules\Api\Support\Endpoint;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;

#[Endpoint(
    description: 'Check if the current token is valid.',
    errors: [ErrorCode::unauthorized],
    response_schema: Authorized::class,
)]
readonly class AuthenticatedController
{
    public function __invoke(): JsonResponse
    {
        if (! auth('sanctum')->check()) {
            return api_response()->unauthorized();
        }

        return api_response()->ok(Authorized::from());
    }
}
