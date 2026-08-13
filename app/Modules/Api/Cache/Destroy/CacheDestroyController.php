<?php

namespace App\Modules\Api\Cache\Destroy;

use App\Models\CacheEntry;
use App\Modules\Api\Cache\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use Illuminate\Http\JsonResponse;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheDestroyController
{
    #[ApiSchema(static function (): array {
        return CacheDestroySchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        if (CacheEntry::query()->whereKey($key)->delete() === 0) {
            return api_response()->notFound(ErrorCode::cache_entry_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheDestroyResponse::from());
    }
}
