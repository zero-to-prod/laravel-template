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
        Table::name => 'roles',
        Table::collate => 'utf8mb4_unicode_ci',
        Table::indexes => [
            'roles_name_guard_name_unique' => [
                self::name,
                self::guard_name,
            ],
        ],
    ])]
enum Roles: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The unique identifier of the role',
        Column::type => ColumnType::bigint->value,
        Column::nullable => false,
        Column::primary_key => true,
        Column::auto_increment => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::name,
        Column::comment => 'The name the role is granted by',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case name = 'name';

    #[Column([
        Column::name => self::guard_name,
        Column::comment => 'The authentication guard the role applies to',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case guard_name = 'guard_name';

    #[Column([
        Column::name => self::created_at,
        Column::comment => 'When the role was created',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case created_at = 'created_at';

    #[Column([
        Column::name => self::updated_at,
        Column::comment => 'When the role was last updated',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case updated_at = 'updated_at';
}
