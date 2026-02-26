<?php

namespace App\Modules\Api;

use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;

readonly class Api
{
    public function unprocessableEntity(Validator $Validator): JsonResponse
    {
        return $this->respond(ApiResponse::fromValidator($Validator), 422);
    }

    public function ok(mixed $data = []): JsonResponse
    {
        return $this->respond(ApiResponse::ok($this->resolveType($data), $data), 200);
    }

    public function unauthorized(ErrorCode $ErrorCode = ErrorCode::unauthorized): JsonResponse
    {
        return $this->respond(ApiResponse::error($ErrorCode->value, [$ErrorCode->value]), 401);
    }

    public function notFound(ErrorCode $ErrorCode, mixed $data = []): JsonResponse
    {
        return $this->respond(ApiResponse::error($ErrorCode->value, [$ErrorCode->value], $data), 404);
    }

    public function conflict(ErrorCode $ErrorCode): JsonResponse
    {
        return $this->respond(ApiResponse::error($ErrorCode->value, [$ErrorCode->value]), 409);
    }

    public function created(mixed $data = []): JsonResponse
    {
        return $this->respond(ApiResponse::ok($this->resolveType($data), $data), 201);
    }

    private function respond(ApiResponse $ApiResponse, int $status): JsonResponse
    {
        return response()->json(
            data: array_filter($ApiResponse->toArray(), static fn (mixed $value) => ! empty($value) || is_bool($value)),
            status: $status
        );
    }

    private function resolveType(mixed $data): string
    {
        if (is_object($data)) {
            return class_basename($data);
        }

        return '';
    }
}
