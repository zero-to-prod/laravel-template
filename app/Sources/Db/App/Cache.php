<?php

namespace App\Sources\Db\App;

use App\Helpers\DataModel;
use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Column;
use App\Sources\Db\Support\ColumnType;
use App\Sources\Db\Support\Table;

#[Table(
    schema: App::class,
    attributes: [
        Table::name => 'cache',
        Table::collate => Collation::utf8mb4_unicode_ci->value,
    ])]
readonly class Cache
{
    use DataModel;

    public const string key = 'key';

    #[Column([
        Column::name => self::key,
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    public string $key;

    public const string value = 'value';

    #[Column([
        Column::name => self::value,
        Column::type => ColumnType::mediumtext->value,
        Column::nullable => false,
    ])]
    public string $value;

    public const string expiration = 'expiration';

    #[Column([
        Column::name => self::expiration,
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    public int $expiration;
}
