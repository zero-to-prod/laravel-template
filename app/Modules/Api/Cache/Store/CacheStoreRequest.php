<?php

namespace App\Modules\Api\Cache\Store;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\HasRequestSchema;
use App\Sources\Db\App\Cache;

readonly class CacheStoreRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string key = 'key';

    #[Request([
        Request::schema => static function (): array {
            return Cache::key->schema();
        },
        Request::required => true,
    ])]
    public string $key;

    public const string value = 'value';

    #[Request([
        Request::schema => static function (): array {
            return Cache::value->schema();
        },
        Request::required => true,
    ])]
    public string $value;

    public const string expiration = 'expiration';

    #[Request([
        Request::schema => static function (): array {
            return Cache::expiration->schema();
        },
        Request::required => true,
    ])]
    public int $expiration;
}
