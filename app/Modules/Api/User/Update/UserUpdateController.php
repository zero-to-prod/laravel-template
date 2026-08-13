<?php

namespace App\Modules\Api\User\Update;

use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserUpdateController
{
    #[ApiSchema(static function (): array {
        return UserUpdateSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = UserUpdateRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $User = User::authenticated($Request);

        $User->update([Users::name->value => UserUpdateRequest::from($Request->all())->name]);

        return api_response()->ok(UserUpdateResponse::from($User->toArray()));
    }
}
