<?php

namespace App\Modules\Api\Admin\User\Update;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\HasRequestSchema;
use App\Sources\Db\App\Users;

readonly class AdminUserUpdateRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string name = 'name';

    #[Request([
        Request::schema => static function (): array {
            return Users::name->schema();
        },
    ])]
    public string $name;

    public const string email = 'email';

    #[Request([
        Request::schema => static function (): array {
            return Users::email->schema();
        },
    ])]
    public string $email;

    public const string password = 'password';

    #[Request([
        Request::schema => static function (): array {
            return Users::password->schema();
        },
    ])]
    public string $password;
}
