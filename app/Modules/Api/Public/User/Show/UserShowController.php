<?php

namespace App\Modules\Api\Public\User\Show;

use App\Models\User;
use App\Modules\Api\Support\PublicApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class UserShowController
{
    #[PublicApiSchema(static function (): array {
        return UserShowSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        return api_response()->ok(UserShowResponse::from(User::authenticated($Request)->toArray()));
    }
}
