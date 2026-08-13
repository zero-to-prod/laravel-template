<?php

namespace App\Modules\Api\Logout;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class LogoutController
{
    #[ApiSchema(static function (): array {
        return LogoutSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        User::authenticated($Request)->currentAccessToken()->delete();

        return api_response()->ok(LogoutResponse::from());
    }
}
