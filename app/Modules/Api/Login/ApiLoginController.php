<?php

namespace App\Modules\Api\Login;

use App\DataModels\User;
use App\Models\User as UserModel;
use App\Modules\Api\Models\ApiToken;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Modules\Api\Support\ErrorCode;
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

        $User = UserModel::query()->where(User::email, $ApiLoginRequest->email)->first();

        if (! $User || ! $User->matchesPassword($ApiLoginRequest->password)) {
            return api_response()->unauthorized(ErrorCode::invalid_credentials);
        }

        return api_response()->ok(
            ApiToken::from([
                ApiToken::token => $User->createToken($ApiLoginRequest->device_name)->plainTextToken,
            ]),
        );
    }
}
