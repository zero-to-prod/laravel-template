<?php

namespace App\Modules\Api\User\Show;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserShowController
{
    /**
     * @throws ReflectionException
     * @throws AuthenticationException
     */
    #[ApiSchema(static function (): array {
        return UserShowSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        return api_response()->ok(UserShowResponse::from(User::authenticated($Request)->toArray()));
    }
}
