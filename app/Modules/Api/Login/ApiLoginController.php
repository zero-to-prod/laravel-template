<?php

namespace App\Modules\Api\Login;

use App\DataModels\User;
use App\Models\User as UserModel;
use App\Modules\Api\Models\ApiToken;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\Support\RequestSchema;
use Illuminate\Http\JsonResponse;
use ReflectionException;
use ZeroToProd\SchemaValidator\SchemaValidator;

readonly class ApiLoginController
{
    /** @throws ReflectionException */
    #[RequestSchema(ApiLoginSchema::class)]
    public function __invoke(): JsonResponse
    {
        $ApiLoginRequest = ApiLoginRequest::from(request()->all());
        $Validator = SchemaValidator::make($ApiLoginRequest->toArray(), ApiLoginRequest::rules());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

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
