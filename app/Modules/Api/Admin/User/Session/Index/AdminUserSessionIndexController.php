<?php

namespace App\Modules\Api\Admin\User\Session\Index;

use App\Models\User;
use App\Modules\Admin\Sessions\SessionsQuery;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\Support\PaginationResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminUserSessionIndexController
{
    #[AdminApiSchema(static function (): array {
        return AdminUserSessionIndexSchema::schema();
    })]
    public function __invoke(Request $Request, string $user): JsonResponse
    {
        if (! User::query()->whereKey($user)->exists()) {
            return api_response()->notFound(ErrorCode::user_not_found);
        }

        $Sessions = SessionsQuery::get($user);

        return api_response()->ok(AdminUserSessionIndexResponse::from([
            AdminUserSessionIndexResponse::sessions => collect($Sessions->items())
                ->map(static fn (object $Session): array => AdminUserSessionResponse::from((array) $Session)->toArray())
                ->all(),
            AdminUserSessionIndexResponse::pagination => PaginationResponse::of($Sessions)->toArray(),
        ]));
    }
}
