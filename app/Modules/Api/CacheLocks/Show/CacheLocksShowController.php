<?php

namespace App\Modules\Api\CacheLocks\Show;

use App\Models\CacheLock;
use App\Modules\Api\CacheLocks\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksShowController
{
    #[ApiSchema(static function (): array {
        return CacheLocksShowSchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        $CacheLock = CacheLock::query()->find($key);

        if ($CacheLock === null) {
            return api_response()->notFound(ErrorCode::cache_lock_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheLocksShowResponse::from($CacheLock->toArray()));
    }
}
