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
        Table::name => 'password_reset_tokens',
        Table::collate => 'utf8mb4_unicode_ci',
    ])]
enum PasswordResetTokens: string
{
    use HasColumn;

    #[Column([
        Column::name => self::email,
        Column::comment => 'The email the reset token was issued to',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case email = 'email';

    #[Column([
        Column::name => self::token,
        Column::comment => 'The hashed password reset token',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case token = 'token';

    #[Column([
        Column::name => self::created_at,
        Column::comment => 'When the reset token was issued',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case created_at = 'created_at';
}
