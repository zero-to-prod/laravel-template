<?php

namespace App\DataModels;

use App\DataModels\Fields\GenericEmail;
use App\Helpers\DataModel;
use App\Helpers\DataModelCast;
use App\Helpers\HasRules;
use App\Helpers\Request;
use App\Helpers\Rule;
use App\Modules\Api\Support\Field;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;

readonly class User
{
    use DataModel;
    use HasRules;

    public const string name = 'name';

    #[Describe([
        Describe::cast => [DataModelCast::class, 'sanitize'],
        Field::field => [
            Field::description => static function () {
                return Users::email->comment();
            },
        ],
    ])]
    public string $name;

    public const string email = 'email';

    #[Describe(GenericEmail::describe)]
    #[Request([Request::rules => [GenericEmail::class, 'rules']])]
    public string $email;

    public const string password = 'password';

    #[Describe([
        Field::field => [Field::description => 'User password'],
    ])]
    #[Request([Request::rules => [self::class, 'passwordRules']])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[Describe([
        Describe::nullable => true,
        Field::field => [
            Field::description => 'Confirmation of the password field; must match it',
        ],
    ])]
    public ?string $password_confirmation;

    public const string remember_token = 'remember_token';

    #[Describe([
        Describe::default => false,
        Field::field => [Field::description => 'Remember login session'],
    ])]
    public bool $remember_token;

    public const string email_verified_at = 'email_verified_at';
    public const string created_at = 'created_at';
    public const string updated_at = 'updated_at';

    /** @return list<Rule|string> */
    public static function passwordRules(): array
    {
        return [
            Rule::required,
            Rule::string,
            Rule::max(255),
        ];
    }

    /** @return list<Rule|string> */
    public static function mailboxIdRules(): array
    {
        return [
            Rule::nullable,
            Rule::string,
            Rule::max(255),
        ];
    }
}
