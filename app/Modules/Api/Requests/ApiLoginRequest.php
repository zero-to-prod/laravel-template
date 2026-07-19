<?php

namespace App\Modules\Api\Requests;

use App\DataModels\Fields\GenericEmail;
use App\Helpers\DataModel;
use App\Helpers\HasFieldRules;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class ApiLoginRequest
{
    use DataModel;
    use HasFieldRules;

    public const string email = 'email';

    #[Describe(GenericEmail::describe)]
    public string $email;

    public const string password = 'password';

    #[Describe([
        Field::field => [
            Field::description => 'User password',
            Field::rules => 'required',
        ],
    ])]
    public string $password;

    public const string device_name = 'device_name';

    #[Describe([
        Field::field => [
            Field::description => 'Name of the requesting device',
            Field::rules => 'required',
        ],
    ])]
    public string $device_name;
}
