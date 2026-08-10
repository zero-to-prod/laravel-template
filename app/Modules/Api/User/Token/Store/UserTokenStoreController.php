<?php

namespace App\Modules\Api\User\Token\Store;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserTokenStoreController
{
    /**
     * @throws ReflectionException
     * @throws AuthenticationException
     */
    #[ApiSchema(static function (): array {
        return UserTokenStoreSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = UserTokenStoreRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $UserTokenStoreRequest = UserTokenStoreRequest::from($Request->all());

        $NewAccessToken = User::authenticated($Request)->createToken(
            $UserTokenStoreRequest->name,
            $UserTokenStoreRequest->abilities(),
            $UserTokenStoreRequest->expires_at === null ? null : Carbon::parse($UserTokenStoreRequest->expires_at),
        );

        return api_response()->created(UserTokenStoreResponse::from([
            ...$NewAccessToken->accessToken->toArray(),
            UserTokenStoreResponse::token => $NewAccessToken->plainTextToken,
        ]));
    }
}
