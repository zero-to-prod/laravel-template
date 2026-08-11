<?php

namespace App\Modules\Api\User\Token\Index;

use App\Models\User;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\User\Token\Show\UserTokenShowResponse;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class UserTokenIndexController
{
    /**
     * @throws ReflectionException
     * @throws AuthenticationException
     */
    #[ApiSchema(static function (): array {
        return UserTokenIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Paginator = User::authenticated($Request)
            ->tokens()
            ->oldest(PersonalAccessTokens::id->value)
            ->paginate(PaginationParameters::perPage($Request));

        return api_response()->ok(UserTokenIndexResponse::from([
            // Through the show response, so the list and the single token are
            // the same object down to the field the model would have leaked.
            UserTokenIndexResponse::tokens => array_map(
                static fn (PersonalAccessToken $Token): array => UserTokenShowResponse::from($Token->toArray())->toArray(),
                $Paginator->items(),
            ),
            UserTokenIndexResponse::pagination => PaginationResponse::of($Paginator)->toArray(),
        ]));
    }
}
