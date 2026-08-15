<?php

namespace App\Modules\Api\Admin\User\Session\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\Sessions;

readonly class AdminUserSessionResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string id = 'id';

    #[Response([Response::schema => static function (): array {
        return Sessions::id->schema();
    }])]
    public string $id;

    public const string ip_address = 'ip_address';

    #[Response([Response::schema => static function (): array {
        return Sessions::ip_address->schema();
    }])]
    public ?string $ip_address;

    public const string user_agent = 'user_agent';

    #[Response([Response::schema => static function (): array {
        return Sessions::user_agent->schema();
    }])]
    public ?string $user_agent;

    public const string last_activity = 'last_activity';

    #[Response([Response::schema => static function (): array {
        return Sessions::last_activity->schema();
    }])]
    public int $last_activity;
}
