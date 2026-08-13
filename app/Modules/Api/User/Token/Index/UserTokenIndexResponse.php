<?php

namespace App\Modules\Api\User\Token\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\Support\Response;
use App\Modules\Api\User\Token\Show\UserTokenShowResponse;
use ZeroToProd\SchemaValidator\Schema;

readonly class UserTokenIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string tokens = 'tokens';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => static function (): array {
        return [
            Schema::type => Schema::array,
            Schema::items => UserTokenShowResponse::data(),
        ];
    }])]
    public array $tokens;

    public const string pagination = 'pagination';

    /** @var array<string, mixed> */
    #[Response([Response::schema => static function (): array {
        return PaginationResponse::data();
    }])]
    public array $pagination;
}
