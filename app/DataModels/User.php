<?php

namespace App\DataModels;

use App\DataModels\Fields\GenericEmail;
use App\DataModels\Fields\GenericString;
use App\Helpers\DataModel;
use App\Helpers\DataModelCast;
use App\Helpers\DescribesFields;
use App\Helpers\HasFieldRules;
use App\Helpers\Rule;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class User implements DescribesFields
{
    use DataModel;
    use HasFieldRules;

    public const string name = 'name';

    #[Describe([
        Describe::cast => [DataModelCast::class, 'sanitize'],
        Field::field => [
            Field::description => "The user's full display name",
            Field::rules => [GenericString::class, 'rules'],
            Field::legend => 'Full Name',
            Field::placeholder => 'First and Last Name',
            Field::icon => 'user',
        ],
    ])]
    public string $name;

    public const string email = 'email';

    #[Describe(GenericEmail::describe)]
    public string $email;

    public const string password = 'password';

    #[Describe([
        Field::field => [
            Field::description => 'User password',
            Field::rules => [self::class, 'passwordRules'],
            Field::sensitive => true,
            Field::legend => 'Password',
            Field::placeholder => 'Password',
            Field::icon => 'key',
        ],
    ])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[Describe([
        Describe::nullable => true,
        Field::field => [
            Field::description => 'Confirmation of the password field; must match it',
            Field::sensitive => true,
            Field::legend => 'Password Confirmation',
            Field::placeholder => 'Password Confirmation',
            Field::icon => 'key',
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
