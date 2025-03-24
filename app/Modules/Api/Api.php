<?php

namespace App\Modules\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;

class Api
{
    public function unprocessableEntity(Validator $Validator): JsonResponse
    {
        return response()->json(ApiResponse::fromValidator($Validator), 422);
    }

    public function ok(ResponseType $ResponseType, mixed $data = []): JsonResponse
    {
        return response()->json(ApiResponse::ok($ResponseType, $data));
    }

    public function unauthorized(string $message = 'unauthorized'): JsonResponse
    {
        return response()->json(ApiResponse::error($message, [$message]), 401);
    }
}