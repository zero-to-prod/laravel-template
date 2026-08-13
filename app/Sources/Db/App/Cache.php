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
        Table::name => 'cache',
        Table::collate => 'utf8mb4_unicode_ci',
    ])]
enum Cache: string
{
    use HasColumn;

    #[Column([
        Column::name => self::key,
        Column::comment => 'The cache key',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case key = 'key';

    #[Column([
        Column::name => self::value,
        Column::comment => 'The serialized cached value',
        Column::type => ColumnType::mediumtext->value,
        Column::nullable => false,
    ])]
    case value = 'value';

    #[Column([
        Column::name => self::expiration,
        Column::comment => 'The unix timestamp the entry expires at',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case expiration = 'expiration';
}
