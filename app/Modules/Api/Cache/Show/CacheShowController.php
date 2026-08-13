<?php

namespace App\Modules\Api\Cache\Show;

use App\Models\CacheEntry;
use App\Modules\Api\Cache\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheShowController
{
    #[ApiSchema(static function (): array {
        return CacheShowSchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        $CacheEntry = CacheEntry::query()->find($key);

        if ($CacheEntry === null) {
            return api_response()->notFound(ErrorCode::cache_entry_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheShowResponse::from($CacheEntry->toArray()));
    }
}
