<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Models\Logout;
use App\Modules\Api\Support\Endpoint;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;

#[Endpoint(
    description: 'Revoke the current API token.',
    errors: [ErrorCode::unauthorized],
    request_schema: Logout::class, response_schema: Logout::class,
)]
class LogoutController
{
    public function __invoke(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return api_response()->ok(Logout::from());
    }
}