<?php

namespace App\Modules\Api\User\Update;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\Users;

readonly class UserUpdateResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string id = 'id';

    #[Response([Response::schema => static function () {
        return Users::id->schema();
    }])]
    public string $id;

    public const string name = 'name';

    #[Response([Response::schema => static function () {
        return Users::name->schema();
    }])]
    public string $name;
}
