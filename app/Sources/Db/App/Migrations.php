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
        Table::name => 'migrations',
        Table::collate => 'utf8mb4_unicode_ci',
    ])]
enum Migrations: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::type => ColumnType::int->value,
        Column::nullable => false,
        Column::primary_key => true,
        Column::auto_increment => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::migration,
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case migration = 'migration';

    #[Column([
        Column::name => self::batch,
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case batch = 'batch';
}
