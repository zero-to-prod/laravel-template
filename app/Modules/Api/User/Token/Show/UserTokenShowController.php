<?php

namespace App\Modules\Api\User\Token\Show;

use App\Models\User;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\User\Token\TokenParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserTokenShowController
{
    #[ApiSchema(static function (): array {
        return UserTokenShowSchema::schema();
    })]
    public function __invoke(Request $Request, string $token): JsonResponse
    {
        $Token = User::authenticated($Request)->tokens()->whereKey($token)->first();

        if ($Token === null) {
            return api_response()->notFound(ErrorCode::token_not_found, [TokenParameter::name => $token]);
        }

        return api_response()->ok(UserTokenShowResponse::from($Token->toArray()));
    }
}
