<?php

namespace App\Modules\Api\Admin\User\Delete;

use App\Models\User;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminUserDeleteController
{
    #[AdminApiSchema(static function (): array {
        return AdminUserDeleteSchema::schema();
    })]
    public function __invoke(Request $Request, string $user): JsonResponse
    {
        $User = User::query()->find($user);

        if (! $User instanceof User) {
            return api_response()->notFound(ErrorCode::user_not_found);
        }

        $Response = AdminUserDeleteResponse::from($User->toArray());
        $User->delete();

        return api_response()->ok($Response);
    }
}
