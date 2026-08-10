<?php

namespace App\Modules\Api\Requests;

use App\Helpers\DataModel;
use App\Helpers\HasRequestSchema;
use App\Helpers\Request;
use App\Sources\Db\App\Users;
use ZeroToProd\SchemaValidator\Property;

readonly class ApiLoginRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string email = 'email';

    #[Request([
        Request::schema => static function (): array {
            return [
                ...Users::email->schema(),
                Property::format => Property::email,
            ];
        },
        Request::required => true,
    ])]
    public string $email;

    public const string password = 'password';

    #[Request([
        Request::schema => [
            Property::type => Property::string,
            Property::maxLength => 255,
            Property::description => 'User password',
        ],
        Request::required => true,
    ])]
    public string $password;

    public const string device_name = 'device_name';

    #[Request([
        Request::schema => [
            Property::type => Property::string,
            Property::maxLength => 255,
            Property::description => 'Name of the requesting device',
        ],
        Request::required => true,
    ])]
    public string $device_name;
}
