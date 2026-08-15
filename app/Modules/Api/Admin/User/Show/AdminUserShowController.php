<?php

namespace App\Modules\Api\Admin\User\Show;

use App\Models\User;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminUserShowController
{
    #[AdminApiSchema(static function (): array {
        return AdminUserShowSchema::schema();
    })]
    public function __invoke(Request $Request, string $user): JsonResponse
    {
        $User = User::query()->find($user);

        if (! $User instanceof User) {
            return api_response()->notFound(ErrorCode::user_not_found);
        }

        return api_response()->ok(AdminUserShowResponse::from($User->toArray()));
    }
}
