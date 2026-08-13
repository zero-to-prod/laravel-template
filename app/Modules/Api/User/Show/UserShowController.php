<?php

namespace App\Modules\Api\User\Show;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserShowController
{
    #[ApiSchema(static function (): array {
        return UserShowSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        return api_response()->ok(UserShowResponse::from(User::authenticated($Request)->toArray()));
    }
}
