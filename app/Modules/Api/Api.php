<?php

namespace App\Modules\Api;

use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;

readonly class Api
{
    public function unprocessableEntity(Validator $Validator, mixed $data = []): JsonResponse
    {
        return $this->respond(ApiResponse::fromValidator($Validator, data: $data), 422);
    }

    public function ok(mixed $data = [], ?array $fields = null): JsonResponse
    {
        $type = $this->resolveType($data);
        $data = $fields ? $this->filterFields($data, $fields) : $data;

        return $this->respond(ApiResponse::ok($type, $data), 200);
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

    public function created(mixed $data = [], ?array $fields = null): JsonResponse
    {
        $type = $this->resolveType($data);
        $data = $fields ? $this->filterFields($data, $fields) : $data;

        return $this->respond(ApiResponse::ok($type, $data), 201);
    }

    private function respond(ApiResponse $ApiResponse, int $status): JsonResponse
    {
        return response()->json(
            data: array_filter($ApiResponse->toArray(), static fn (mixed $value) => ! empty($value) || is_bool($value)),
            status: $status
        );
    }

    private function filterFields(mixed $data, array $fields): array
    {
        $array = is_object($data) && method_exists($data, 'toArray')
            ? $data->toArray()
            : (array) $data;

        $result = [];

        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                if (array_key_exists($value, $array)) {
                    $result[$value] = $array[$value];
                }
            } elseif (is_array($value) && array_key_exists($key, $array)) {
                $result[$key] = is_array($array[$key])
                    ? array_map(fn (mixed $item) => $this->filterFields($item, $value), $array[$key])
                    : $this->filterFields($array[$key], $value);
            }
        }

        return $result;
    }

    private function resolveType(mixed $data): string
    {
        if (is_object($data)) {
            return class_basename($data);
        }

        return '';
    }
}
