<?php

namespace App\Modules\Api\Login;

use App\Helpers\DataModelFromRequest;
use Illuminate\Support\Facades\Validator;
use Zerotoprod\DataModel\Describe;

readonly class ApiLoginForm
{
    use DataModelFromRequest;

    /** @link $email */
    public const email = 'email';
    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    public string $email;

    /** @link $password */
    public const password = 'password';
    public string $password;

    /** @link $device_name */
    public const device_name = 'device_name';
    public string $device_name;

    public function validator(): \Illuminate\Validation\Validator
    {
        return Validator::make($this->toArray(), [
            self::email => 'required|email',
            self::password => 'required',
            self::device_name => 'required',
        ]);
    }
}