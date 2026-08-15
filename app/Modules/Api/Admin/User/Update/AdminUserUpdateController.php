<?php

namespace App\Modules\Api\Admin\User\Update;

use App\Models\User;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminUserUpdateController
{
    #[AdminApiSchema(static function (): array {
        return AdminUserUpdateSchema::schema();
    })]
    public function __invoke(Request $Request, string $user): JsonResponse
    {
        $Validator = AdminUserUpdateRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $User = User::query()->find($user);

        if (! $User instanceof User) {
            return api_response()->notFound(ErrorCode::user_not_found);
        }

        $User->update($Validator->validated());

        return api_response()->ok(AdminUserUpdateResponse::from($User->toArray()));
    }
}
