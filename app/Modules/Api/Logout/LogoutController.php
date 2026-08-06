<?php

namespace App\Modules\Api\Logout;

use App\Models\User;
use App\Modules\Api\Models\Logout;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class LogoutController
{
    /**
     * @throws ReflectionException
     * @throws AuthenticationException
     */
    #[ApiSchema(LogoutSchema::schema)]
    public function __invoke(Request $Request): JsonResponse
    {
        User::authenticated($Request)->currentAccessToken()->delete();

        return api_response()->ok(Logout::from());
    }
}
