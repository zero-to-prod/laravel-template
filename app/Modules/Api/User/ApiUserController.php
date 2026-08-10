<?php

namespace App\Modules\Api\User;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class ApiUserController
{
    /**
     * @throws ReflectionException
     * @throws AuthenticationException
     */
    #[ApiSchema(static function (): array {
        return ApiUserSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        return api_response()->ok(ApiUserResponse::from(User::authenticated($Request)->toArray()));
    }
}
