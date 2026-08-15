<?php

namespace App\Modules\Api\Admin\User\Session\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\Support\Response;
use ZeroToProd\SchemaValidator\Schema;

readonly class AdminUserSessionIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string sessions = 'sessions';

    /** @var list<object> */
    #[Response([Response::schema => static function (): array {
        return [
            Schema::type => Schema::array,
            Schema::items => AdminUserSessionResponse::data(),
        ];
    }])]
    public array $sessions;

    public const string pagination = 'pagination';

    /** @var array<string, mixed> */
    #[Response([Response::schema => static function (): array {
        return PaginationResponse::data();
    }])]
    public array $pagination;
}
