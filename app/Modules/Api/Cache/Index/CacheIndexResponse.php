<?php

namespace App\Modules\Api\Cache\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Cache\Show\CacheShowResponse;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\Support\Response;
use ZeroToProd\SchemaValidator\Schema;

readonly class CacheIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string entries = 'entries';

    /**
     * The items are the object the show endpoint publishes, read off that
     * class rather than restated, so the two can never drift.
     *
     * @var list<array<string, mixed>>
     */
    #[Response([Response::schema => static function (): array {
        return [
            Schema::type => Schema::array,
            Schema::items => CacheShowResponse::data(),
        ];
    }])]
    public array $entries;

    public const string pagination = 'pagination';

    /** @var array<string, mixed> */
    #[Response([Response::schema => static function (): array {
        return PaginationResponse::data();
    }])]
    public array $pagination;
}
