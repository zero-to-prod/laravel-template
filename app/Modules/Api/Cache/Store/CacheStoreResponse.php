<?php

namespace App\Modules\Api\Cache\Store;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\Cache;

readonly class CacheStoreResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string key = 'key';

    #[Response([Response::schema => static function () {
        return Cache::key->schema();
    }])]
    public string $key;

    public const string value = 'value';

    #[Response([Response::schema => static function () {
        return Cache::value->schema();
    }])]
    public string $value;

    public const string expiration = 'expiration';

    #[Response([Response::schema => static function () {
        return Cache::expiration->schema();
    }])]
    public int $expiration;
}
