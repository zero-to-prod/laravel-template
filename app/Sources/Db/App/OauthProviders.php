<?php

declare(strict_types=1);

namespace App\Sources\Db\App;

use App\Sources\Db\HasColumn;
use ZeroToProd\DbModel\Column;
use ZeroToProd\DbModel\ColumnType;
use ZeroToProd\DbModel\Table;

/**
 * @method string type()
 * @method string|null comment()
 * @method int|null length()
 * @method bool|null nullable()
 * @method bool|null unique()
 * @method bool|null primary_key()
 * @method bool|null auto_increment()
 */
#[Table(
    schema: App::class,
    attributes: [
        Table::name => 'oauth_providers',
        Table::collate => 'utf8mb4_unicode_ci',
        Table::indexes => [
            'oauth_providers_user_id_foreign' => [
                self::user_id,
            ],
        ],
    ])]
enum OauthProviders: string
{
    use HasColumn;

    #[Column([
        Column::name => self::user_id,
        Column::comment => 'The user the OAuth identity belongs to',
        Column::type => ColumnType::char->value,
        Column::length => 26,
        Column::nullable => false,
    ])]
    case user_id = 'user_id';

    #[Column([
        Column::name => self::provider_id,
        Column::comment => 'The OAuth provider identifier',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case provider_id = 'provider_id';

    #[Column([
        Column::name => self::sub,
        Column::comment => 'The provider subject identifier',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::unique => true,
    ])]
    case sub = 'sub';

    #[Column([
        Column::name => self::name,
        Column::comment => 'The name supplied by the provider',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case name = 'name';

    #[Column([
        Column::name => self::given_name,
        Column::comment => 'The given name supplied by the provider',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case given_name = 'given_name';

    #[Column([
        Column::name => self::family_name,
        Column::comment => 'The family name supplied by the provider',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case family_name = 'family_name';

    #[Column([
        Column::name => self::picture,
        Column::comment => 'The profile picture URL supplied by the provider',
        Column::type => ColumnType::text->value,
        Column::nullable => false,
    ])]
    case picture = 'picture';

    #[Column([
        Column::name => self::email,
        Column::comment => 'The email supplied by the provider',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case email = 'email';

    #[Column([
        Column::name => self::email_verified,
        Column::comment => 'Whether the provider verified the email',
        Column::type => ColumnType::tinyint->value,
        Column::nullable => false,
    ])]
    case email_verified = 'email_verified';

    #[Column([
        Column::name => self::hd,
        Column::comment => 'The hosted domain supplied by the provider',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => true,
    ])]
    case hd = 'hd';

    #[Column([
        Column::name => self::id,
        Column::comment => 'The compatibility identifier supplied by Socialite',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::verified_email,
        Column::comment => 'The compatibility email verification flag supplied by Socialite',
        Column::type => ColumnType::tinyint->value,
        Column::nullable => false,
    ])]
    case verified_email = 'verified_email';

    #[Column([
        Column::name => self::link,
        Column::comment => 'The profile URL supplied by Socialite',
        Column::type => ColumnType::text->value,
        Column::nullable => true,
    ])]
    case link = 'link';
}
