<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Api;
use App\Modules\Api\ResponseType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController
{
    public function __invoke(Api $Api, Request $Request): JsonResponse
    {
        $Request->user()->currentAccessToken()->delete();

        return $Api->ok(ResponseType::logout);
    }
}