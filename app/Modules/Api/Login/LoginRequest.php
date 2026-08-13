<?php

namespace App\Modules\Api\Login;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\HasRequestSchema;
use App\Sources\Db\App\PersonalAccessTokens;
use App\Sources\Db\App\Users;
use ZeroToProd\SchemaValidator\Property;

readonly class LoginRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string email = 'email';

    #[Request([
        Request::schema => static function (): array {
            return [
                ...Users::email->schema(),
                Property::format => Property::email,
                Property::description => 'User email',
            ];
        },
        Request::required => true,
    ])]
    public string $email;

    public const string password = 'password';

    #[Request([
        Request::schema => static function (): array {
            return [
                ...Users::password->schema(),
                Property::description => 'User password',
            ];
        },
        Request::required => true,
    ])]
    public string $password;

    public const string device_name = 'device_name';

    #[Request([
        Request::schema => static function (): array {
            return [
                ...PersonalAccessTokens::name->schema(),
                Property::description => 'Name of the requesting device',
            ];
        },
        Request::required => true,
    ])]
    public string $device_name;
}
