<?php

namespace App\Modules\Api\Login;

use App\Models\User;
use App\Modules\Api\Support\ErrorCode;
use App\Sources\Db\App\Users;
use Illuminate\Http\JsonResponse;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class LoginController
{
    /** @throws ReflectionException */
    #[ApiSchema(static function (): array {
        return LoginSchema::schema();
    })]
    public function __invoke(): JsonResponse
    {
        $Validator = LoginRequest::validator(request()->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $ApiLoginRequest = LoginRequest::from(request()->all());

        $User = User::query()->where(Users::email->value, $ApiLoginRequest->email)->first();

        if (! $User || ! $User->matchesPassword($ApiLoginRequest->password)) {
            return api_response()->unauthorized(ErrorCode::invalid_credentials);
        }

        return api_response()->ok(
            LoginResponse::from([
                LoginResponse::token => $User->createToken($ApiLoginRequest->device_name)->plainTextToken,
            ]),
        );
    }
}
