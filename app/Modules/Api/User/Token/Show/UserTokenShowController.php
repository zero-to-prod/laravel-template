<?php

namespace App\Modules\Api\User\Token\Show;

use App\Models\User;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\User\Token\TokenParameter;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserTokenShowController
{
    /**
     * @throws ReflectionException
     * @throws AuthenticationException
     */
    #[ApiSchema(static function (): array {
        return UserTokenShowSchema::schema();
    })]
    public function __invoke(Request $Request, string $token): JsonResponse
    {
        // Scoped to the caller's own tokens, so somebody else's id is a 404
        // rather than a 403: the answer must not tell them the token exists.
        $Token = User::authenticated($Request)->tokens()->whereKey($token)->first();

        if ($Token === null) {
            return api_response()->notFound(ErrorCode::token_not_found, [TokenParameter::name => $token]);
        }

        return api_response()->ok(UserTokenShowResponse::from($Token->toArray()));
    }
}
