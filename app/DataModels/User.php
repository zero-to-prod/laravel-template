<?php

namespace App\DataModels;

use App\DataModels\Fields\GenericEmail;
use App\Helpers\DataModel;
use App\Helpers\DataModelCast;
use App\Helpers\HasFieldRules;
use App\Helpers\Rule;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class User
{
    use DataModel;
    use HasFieldRules;

    public const string name = 'name';

    #[Describe([
        Describe::cast => [DataModelCast::class, 'sanitize'],
        Field::field => [
            Field::description => "The user's full display name",
        ],
    ])]
    public string $name;

    public const string handle = 'handle';

    #[Describe([
        Describe::cast => [DataModelCast::class, 'sanitizeEmail'],
        Field::field => [
            Field::description => "The user's unique slug, used as the local part of every mailbox address",
        ],
    ])]
    public string $handle;

    public const string email = 'email';

    #[Describe(GenericEmail::describe)]
    public string $email;

    public const string password = 'password';

    #[Describe([
        Field::field => [
            Field::description => 'User password',
            Field::rules => [self::class, 'passwordRules'],
        ],
    ])]
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

    public const string mailbox_id = 'mailbox_id';

    #[Describe([
        Describe::nullable => true,
        Describe::cast => [DataModelCast::class, 'sanitizeNullable'],
        Field::field => [
            Field::description => 'References mailboxes.address — the mailbox pre-filled as the default "from" address across compose forms',
            Field::rules => [self::class, 'mailboxIdRules'],
        ],
    ])]
    public ?string $mailbox_id;

    public const string email_verified_at = 'email_verified_at';
    public const string created_at = 'created_at';
    public const string updated_at = 'updated_at';

    public static function passwordRules(): array
    {
        return [
            Rule::required,
            Rule::string,
            Rule::max(255),
        ];
    }

    public static function mailboxIdRules(): array
    {
        return [
            Rule::nullable,
            Rule::string,
            Rule::max(255),
        ];
    }
}
