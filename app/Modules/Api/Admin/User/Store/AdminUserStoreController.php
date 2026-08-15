<?php

namespace App\Modules\Api\Admin\User\Store;

use App\Models\User;
use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminUserStoreController
{
    #[AdminApiSchema(static function (): array {
        return AdminUserStoreSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = AdminUserStoreRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $User = User::query()->create($Validator->validated());

        return api_response()->created(AdminUserStoreResponse::from($User->toArray()));
    }
}
