<?php

namespace App\Modules\PasswordReset;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Sources\Db\App\Users;
use Illuminate\Validation\Rules\Password;
use Zerotoprod\DataModel\Describe;

readonly class ResetPasswordRequest
{
    use DataModel;
    use IsRequest;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[Request([Request::rules => [self::class, 'emailRules']])]
    public string $email;

    /** @return list<string|Rule> */
    public static function emailRules(): array
    {
        return [
            ...Users::email->rules(),
            Rule::email,
        ];
    }

    public const string password = 'password';

    #[Request([Request::rules => [self::class, 'passwordRules']])]
    public string $password;

    /** @return list<Rule|Password> */
    public static function passwordRules(): array
    {
        return [
            Rule::required,
            Rule::confirmed,
            Password::defaults(),
        ];
    }

    public const string password_confirmation = 'password_confirmation';

    #[Describe([Describe::default => ''])]
    public string $password_confirmation;

    public const string token = 'token';

    #[Request([Request::rules => 'required|string'])]
    public string $token;
}
