<?php

namespace App\Modules\Settings\Authentication;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use Illuminate\Validation\Rules\Password;
use Zerotoprod\DataModel\Describe;

readonly class PasswordRequest
{
    use DataModel;
    use IsRequest;

    public const string current_password = 'current_password';

    #[Request([Request::rules => static function () {
        return [
            Rule::required,
            Rule::current_password,
        ];
    }])]
    public string $current_password;

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
