<?php

namespace App\Modules\Api\CacheLocks\Store;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\CacheLocks;

readonly class CacheLocksStoreResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string key = 'key';

    #[Response([Response::schema => static function () {
        return CacheLocks::key->schema();
    }])]
    public string $key;

    public const string owner = 'owner';

    #[Response([Response::schema => static function () {
        return CacheLocks::owner->schema();
    }])]
    public string $owner;

    public const string expiration = 'expiration';

    #[Response([Response::schema => static function () {
        return CacheLocks::expiration->schema();
    }])]
    public int $expiration;
}
