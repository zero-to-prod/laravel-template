<?php

namespace App\Modules\Api\Cache\Show;

use App\Modules\Api\Cache\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use App\Sources\Db\App\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheShowController
{
    /**
     * @throws ReflectionException
     */
    #[ApiSchema(static function (): array {
        return CacheShowSchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        $Entry = DB::table(Cache::table())->where(Cache::key->value, $key)->first();

        if ($Entry === null) {
            return api_response()->notFound(ErrorCode::cache_entry_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheShowResponse::from((array) $Entry));
    }
}
