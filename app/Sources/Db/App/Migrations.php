<?php

namespace App\Sources\Db\App;

use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Column;
use App\Sources\Db\Support\ColumnType;
use App\Sources\Db\Support\HasColumnAttribute;
use App\Sources\Db\Support\Table;

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
        Table::collate => Collation::utf8mb4_unicode_ci->value,
    ])]
enum Migrations: string
{
    use HasColumnAttribute;

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
