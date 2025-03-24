<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Api;
use App\Modules\Api\Login\ApiLoginForm;
use App\Modules\Api\ResponseType;
use Illuminate\Http\JsonResponse;

class AuthenticatedController
{
    public function __invoke(Api $Api, ApiLoginForm $ApiLoginForm): JsonResponse
    {
        if (!auth('sanctum')->check()) {
            return $Api->unauthorized();
        }

        return $Api->ok(ResponseType::authorized);
    }
}