<?php

namespace App\Modules\PasswordReset;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;

readonly class ForgotPasswordRequest
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
}
