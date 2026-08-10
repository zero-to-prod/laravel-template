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
        Table::name => 'cache_locks',
        Table::collate => Collation::utf8mb4_unicode_ci->value,
    ])]
enum CacheLocks: string
{
    use HasColumnAttribute;

    #[Column([
        Column::name => self::key,
        Column::comment => 'The name of the lock',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case key = 'key';

    #[Column([
        Column::name => self::owner,
        Column::comment => 'The identifier of the process holding the lock',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case owner = 'owner';

    #[Column([
        Column::name => self::expiration,
        Column::comment => 'The unix timestamp the lock expires at',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case expiration = 'expiration';
}
