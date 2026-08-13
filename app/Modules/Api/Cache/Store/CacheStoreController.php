<?php

namespace App\Modules\Api\Cache\Store;

use App\Models\CacheEntry;
use App\Sources\Db\App\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheStoreController
{
    #[ApiSchema(static function (): array {
        return CacheStoreSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = CacheStoreRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $CacheStoreRequest = CacheStoreRequest::from($Request->all());

        // The key is the primary key, so writing one that is already there
        // replaces it, the way writing to the cache store itself does.
        CacheEntry::query()->updateOrCreate(
            [Cache::key->value => $CacheStoreRequest->key],
            [
                Cache::value->value => $CacheStoreRequest->value,
                Cache::expiration->value => $CacheStoreRequest->expiration,
            ],
        );

        return api_response()->created(CacheStoreResponse::from([
            CacheStoreResponse::key => $CacheStoreRequest->key,
            CacheStoreResponse::value => $CacheStoreRequest->value,
            CacheStoreResponse::expiration => $CacheStoreRequest->expiration,
        ]));
    }
}
