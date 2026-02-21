<?php

namespace App\Modules\Api\Login;

use App\Models\User;
use App\Modules\Api\Models\ApiToken;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Modules\Api\Support\Endpoint;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

#[Endpoint(
    description: 'Authenticate and receive an API token.',
    errors: [ErrorCode::invalid_credentials],
    request_schema: ApiLoginRequest::class,
    response_schema: ApiToken::class,
)]
readonly class ApiLoginController
{
    public function __invoke(): JsonResponse
    {
        $ApiLoginForm = ApiLoginRequest::from(request()->all());
        $Validator = Validator::make($ApiLoginForm->toArray(), $ApiLoginForm->rules());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $User = User::where(User::email, $ApiLoginForm->email)->first();

        if (! $User || ! $User->matchesPassword($ApiLoginForm->password)) {
            return api_response()->unauthorized(ErrorCode::invalid_credentials);
        }

        return api_response()->ok(
            ApiToken::from([
                ApiToken::token => $User->createToken($ApiLoginForm->device_name)->plainTextToken,
            ]),
        );
    }
}