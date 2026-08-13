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
        Table::name => 'model_has_permissions',
        Table::collate => 'utf8mb4_unicode_ci',
        Table::indexes => [
            'model_has_permissions_model_id_model_type_index' => [
                self::model_id,
                self::model_type,
            ],
        ],
    ])]
enum ModelHasPermissions: string
{
    use HasColumn;

    #[Column([
        Column::name => self::permission_id,
        Column::comment => 'The permission that is granted',
        Column::type => ColumnType::char->value,
        Column::length => 26,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case permission_id = 'permission_id';

    #[Column([
        Column::name => self::model_type,
        Column::comment => 'The class of the model the permission is granted to',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case model_type = 'model_type';

    #[Column([
        Column::name => self::model_id,
        Column::comment => 'The identifier of the model the permission is granted to',
        Column::type => ColumnType::char->value,
        Column::length => 26,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case model_id = 'model_id';
}
