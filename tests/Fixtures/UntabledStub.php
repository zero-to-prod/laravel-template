<?php

namespace Tests\Fixtures;

use App\Sources\Db\HasColumn;
use ZeroToProd\DbModel\Column;
use ZeroToProd\DbModel\ColumnType;

/**
 * A table enum that names no table. Every generated one names its own, so this is
 * the only way to reach the branch where there is no name to read — which answers
 * empty rather than throwing.
 */
enum UntabledStub: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::type => ColumnType::char->value,
        Column::length => 26,
        Column::nullable => false,
    ])]
    case id = 'id';
}
