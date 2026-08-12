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
        Table::name => 'users',
        Table::collate => 'utf8mb4_unicode_ci',
    ])]
enum Users: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The unique identifier of the user',
        Column::type => ColumnType::char->value,
        Column::length => 26,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::name,
        Column::comment => 'The users name',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case name = 'name';

    #[Column([
        Column::name => self::email,
        Column::comment => 'The users email',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::unique => true,
    ])]
    case email = 'email';

    #[Column([
        Column::name => self::email_verified_at,
        Column::comment => 'When the users email was verified',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case email_verified_at = 'email_verified_at';

    #[Column([
        Column::name => self::password,
        Column::comment => 'The users hashed password',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case password = 'password';

    #[Column([
        Column::name => self::theme,
        Column::comment => 'The color theme the user prefers',
        Column::type => ColumnType::varchar->value,
        Column::length => 16,
        Column::nullable => false,
    ])]
    case theme = 'theme';

    #[Column([
        Column::name => self::remember_token,
        Column::comment => 'The token that keeps the user signed in between sessions',
        Column::type => ColumnType::varchar->value,
        Column::length => 100,
        Column::nullable => true,
    ])]
    case remember_token = 'remember_token';

    #[Column([
        Column::name => self::created_at,
        Column::comment => 'When the user was created',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case created_at = 'created_at';

    #[Column([
        Column::name => self::updated_at,
        Column::comment => 'When the user was last updated',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case updated_at = 'updated_at';
}
