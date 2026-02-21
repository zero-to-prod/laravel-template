<?php

namespace App\Modules\Api\Requests;

use App\Helpers\DataModel;
use App\Helpers\HasFieldRules;
use App\Modules\Api\Field;
use Zerotoprod\DataModel\Describe;

readonly class ApiLoginRequest
{
    use DataModel;
    use HasFieldRules;

    /** @link $email */
    public const email = 'email';

    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    #[Field(
        description: 'User email address',
        rules: 'required|email'
    )]
    public string $email;

    /** @link $password */
    public const password = 'password';

    #[Field(
        description: 'User password',
        rules: 'required'
    )]
    public string $password;

    /** @link $device_name */
    public const device_name = 'device_name';

    #[Field(
        description: 'Name of the requesting device',
        rules: 'required'
    )]
    public string $device_name;
}