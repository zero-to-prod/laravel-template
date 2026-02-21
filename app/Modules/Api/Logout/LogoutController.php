<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Endpoint;
use App\Modules\Api\ErrorCode;
use App\Modules\Api\Models\Logout;
use Illuminate\Http\JsonResponse;

#[Endpoint(
    description: 'Revoke the current API token.',
    errors: [ErrorCode::unauthorized],
    response: Logout::class,
)]
class LogoutController
{
    public function __invoke(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return api_response()->ok(Logout::from());
    }
}