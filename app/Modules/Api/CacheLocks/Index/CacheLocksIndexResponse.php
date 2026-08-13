<?php

namespace App\Modules\Api\CacheLocks\Index;

use App\Helpers\DataModel;
use App\Modules\Api\CacheLocks\Show\CacheLocksShowResponse;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\Support\Response;
use ZeroToProd\SchemaValidator\Schema;

readonly class CacheLocksIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string locks = 'locks';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => static function (): array {
        return [
            Schema::type => Schema::array,
            Schema::items => CacheLocksShowResponse::data(),
        ];
    }])]
    public array $locks;

    public const string pagination = 'pagination';

    /** @var array<string, mixed> */
    #[Response([Response::schema => static function (): array {
        return PaginationResponse::data();
    }])]
    public array $pagination;
}
