<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Models\Logout;
use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class LogoutController
{
    #[ApiSchema(LogoutSchema::schema)]
    public function __invoke(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return api_response()->ok(Logout::from());
    }
}
