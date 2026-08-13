<?php

namespace App\Modules\Api\User\Update;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\HasRequestSchema;
use App\Sources\Db\App\Users;

readonly class UserUpdateRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string name = 'name';

    #[Request([
        Request::schema => static function (): array {
            return Users::name->schema();
        },
        Request::required => true,
    ])]
    public string $name;
}
