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
        Table::name => 'role_has_permissions',
        Table::collate => 'utf8mb4_unicode_ci',
        Table::indexes => [
            'role_has_permissions_role_id_foreign' => [
                self::role_id,
            ],
        ],
    ])]
enum RoleHasPermissions: string
{
    use HasColumn;

    #[Column([
        Column::name => self::permission_id,
        Column::comment => 'The permission that is granted',
        Column::type => ColumnType::bigint->value,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case permission_id = 'permission_id';

    #[Column([
        Column::name => self::role_id,
        Column::comment => 'The role the permission is granted to',
        Column::type => ColumnType::bigint->value,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case role_id = 'role_id';
}
