<?php

namespace App\Modules\PasswordConfirmation;

use App\Helpers\DataModel;
use App\Helpers\IsRequest;
use App\Helpers\Request;
use App\Helpers\Rule;

readonly class PasswordConfirmationRequest
{
    use DataModel;
    use IsRequest;

    public const string password = 'password';

    #[Request([Request::rules => [self::class, 'passwordRules']])]
    public string $password;

    /** @return list<Rule> */
    public static function passwordRules(): array
    {
        return [
            Rule::required,
            Rule::current_password,
        ];
    }
}
