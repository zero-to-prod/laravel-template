<?php

namespace App\Modules\Api\Cache\Destroy;

use App\Modules\Api\Cache\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use App\Sources\Db\App\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheDestroyController
{
    /**
     * @throws ReflectionException
     */
    #[ApiSchema(static function (): array {
        return CacheDestroySchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        // The delete count is the existence check: a separate read would let
        // the row go between the two.
        if (DB::table(Cache::table())->where(Cache::key->value, $key)->delete() === 0) {
            return api_response()->notFound(ErrorCode::cache_entry_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheDestroyResponse::from());
    }
}
