<?php

namespace App\Modules\Api\Login;

use App\Models\User;
use App\Modules\Api\Api;
use App\Modules\Api\ResponseType;
use Illuminate\Http\JsonResponse;

class ApiLoginController
{
    public function __invoke(Api $Api, ApiLoginForm $ApiLoginForm): JsonResponse
    {
        $Validator = $ApiLoginForm->validator();

        if ($Validator->fails()) {
            return $Api->unprocessableEntity($Validator);
        }

        $User = User::where(User::email, $ApiLoginForm->email)->first();

        if (!$User || !$User->matchesPassword($ApiLoginForm->password)) {
            return $Api->unauthorized('The provided credentials are incorrect.');
        }

        return $Api->ok(
            ResponseType::token,
            ApiTokenResponse::from([
                ApiTokenResponse::token => $User->createToken($ApiLoginForm->device_name)->plainTextToken,
            ])
        );
    }
}