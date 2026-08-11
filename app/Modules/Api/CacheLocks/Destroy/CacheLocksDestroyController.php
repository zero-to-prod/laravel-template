<?php

namespace App\Modules\Api\CacheLocks\Destroy;

use App\Modules\Api\CacheLocks\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use App\Sources\Db\App\CacheLocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksDestroyController
{
    /**
     * @throws ReflectionException
     */
    #[ApiSchema(static function (): array {
        return CacheLocksDestroySchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        if (DB::table(CacheLocks::table())->where(CacheLocks::key->value, $key)->delete() === 0) {
            return api_response()->notFound(ErrorCode::cache_lock_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheLocksDestroyResponse::from());
    }
}
