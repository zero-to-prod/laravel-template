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
        Table::name => 'sessions',
        Table::collate => 'utf8mb4_unicode_ci',
        Table::indexes => [
            'sessions_last_activity_index' => [
                self::last_activity,
            ],
            'sessions_user_id_index' => [
                self::user_id,
            ],
        ],
    ])]
enum Sessions: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The session identifier',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::user_id,
        Column::comment => 'The user the session belongs to',
        Column::type => ColumnType::char->value,
        Column::length => 26,
        Column::nullable => true,
    ])]
    case user_id = 'user_id';

    #[Column([
        Column::name => self::ip_address,
        Column::comment => 'The address the session was last seen from',
        Column::type => ColumnType::varchar->value,
        Column::length => 45,
        Column::nullable => true,
    ])]
    case ip_address = 'ip_address';

    #[Column([
        Column::name => self::user_agent,
        Column::comment => 'The user agent the session was last seen from',
        Column::type => ColumnType::text->value,
        Column::nullable => true,
    ])]
    case user_agent = 'user_agent';

    #[Column([
        Column::name => self::payload,
        Column::comment => 'The serialized session data',
        Column::type => ColumnType::longtext->value,
        Column::nullable => false,
    ])]
    case payload = 'payload';

    #[Column([
        Column::name => self::last_activity,
        Column::comment => 'The unix timestamp of the last request on the session',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case last_activity = 'last_activity';
}
