<?php

namespace App\Modules\Api\CacheLocks\Store;

use App\Models\CacheLock;
use App\Sources\Db\App\CacheLocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksStoreController
{
    #[ApiSchema(static function (): array {
        return CacheLocksStoreSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = CacheLocksStoreRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        $CacheLocksStoreRequest = CacheLocksStoreRequest::from($Request->all());

        CacheLock::query()->updateOrCreate(
            [CacheLocks::key->value => $CacheLocksStoreRequest->key],
            [
                CacheLocks::owner->value => $CacheLocksStoreRequest->owner,
                CacheLocks::expiration->value => $CacheLocksStoreRequest->expiration,
            ],
        );

        return api_response()->created(CacheLocksStoreResponse::from([
            CacheLocksStoreResponse::key => $CacheLocksStoreRequest->key,
            CacheLocksStoreResponse::owner => $CacheLocksStoreRequest->owner,
            CacheLocksStoreResponse::expiration => $CacheLocksStoreRequest->expiration,
        ]));
    }
}
