<?php

namespace App\Modules\Register;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Sources\Db\App\Users;
use Illuminate\Validation\Rules\Password;
use Zerotoprod\DataModel\Describe;

readonly class RegisterRequest
{
    use DataModel;
    use IsRequest;

    public const string name = 'name';

    #[Describe([Describe::cast => [self::class, 'sanitize']])]
    #[Request([Request::rules => static function () {
        return Users::name->rules();
    }])]
    public string $name;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[Request([Request::rules => static function () {
        return [
            ...Users::email->rules(),
            Rule::email,
            Rule::unique('users'),
        ];
    }])]
    public string $email;

    public const string password = 'password';

    #[Request([Request::rules => static function () {
        return [
            Rule::required,
            Rule::confirmed,
            Password::defaults(),
        ];
    }])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[Describe([Describe::default => ''])]
    public string $password_confirmation;
}
