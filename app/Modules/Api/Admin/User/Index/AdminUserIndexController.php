<?php

namespace App\Modules\Api\Admin\User\Index;

use App\Models\User;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\PaginationResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminUserIndexController
{
    #[AdminApiSchema(static function (): array {
        return AdminUserIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Users = User::query()->orderBy('name')->paginate();

        return api_response()->ok(AdminUserIndexResponse::from([
            AdminUserIndexResponse::users => $Users->getCollection()
                ->map(static fn (mixed $User): array => $User instanceof User ? $User->toArray() : [])
                ->all(),
            AdminUserIndexResponse::pagination => PaginationResponse::of($Users)->toArray(),
        ]));
    }
}
