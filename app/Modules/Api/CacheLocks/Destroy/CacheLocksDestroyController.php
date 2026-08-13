<?php

namespace App\Modules\Api\CacheLocks\Destroy;

use App\Models\CacheLock;
use App\Modules\Api\CacheLocks\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksDestroyController
{
    #[ApiSchema(static function (): array {
        return CacheLocksDestroySchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        if (CacheLock::query()->whereKey($key)->delete() === 0) {
            return api_response()->notFound(ErrorCode::cache_lock_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheLocksDestroyResponse::from());
    }
}
