<?php

namespace App\Modules\Api\CacheLocks\Store;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\HasRequestSchema;
use App\Sources\Db\App\CacheLocks;

readonly class CacheLocksStoreRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string key = 'key';

    #[Request([
        Request::schema => static function (): array {
            return CacheLocks::key->schema();
        },
        Request::required => true,
    ])]
    public string $key;

    public const string owner = 'owner';

    #[Request([
        Request::schema => static function (): array {
            return CacheLocks::owner->schema();
        },
        Request::required => true,
    ])]
    public string $owner;

    public const string expiration = 'expiration';

    #[Request([
        Request::schema => static function (): array {
            return CacheLocks::expiration->schema();
        },
        Request::required => true,
    ])]
    public int $expiration;
}
