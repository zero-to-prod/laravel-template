<?php

namespace App\Modules\Login;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;

readonly class LoginRequest
{
    use DataModel;
    use IsRequest;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[Request([Request::rules => static function () {
        return [
            ...Users::email->rules(),
            Rule::email,
        ];
    }])]
    public string $email;

    /** @link $password */
    public const string password = 'password';

    #[Request([Request::rules => static function () {
        return Users::password->rules();
    }])]
    public string $password;

    public const string remember_token = 'remember_token';

    #[Describe([Describe::default => false])]
    public bool $remember_token;
}
