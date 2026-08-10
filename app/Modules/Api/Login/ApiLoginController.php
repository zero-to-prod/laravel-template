<?php

namespace App\Modules\Api\Login;

use App\Models\User;
use App\Modules\Api\Support\ErrorCode;
use App\Sources\Db\App\Users;
use Illuminate\Http\JsonResponse;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class ApiLoginController
{
    /** @throws ReflectionException */
    #[ApiSchema(static function (): array {
        return ApiLoginSchema::schema();
    })]
    public function __invoke(): JsonResponse
    {
        $Validator = ApiLoginRequest::validator(request()->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $ApiLoginRequest = ApiLoginRequest::from(request()->all());

        $User = User::query()->where(Users::email->value, $ApiLoginRequest->email)->first();

        if (! $User || ! $User->matchesPassword($ApiLoginRequest->password)) {
            return api_response()->unauthorized(ErrorCode::invalid_credentials);
        }

        return api_response()->ok(
            ApiLoginResponse::from([
                ApiLoginResponse::token => $User->createToken($ApiLoginRequest->device_name)->plainTextToken,
            ]),
        );
    }
}
