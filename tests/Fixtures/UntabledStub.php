<?php

namespace Tests\Fixtures;

use App\Sources\Db\HasColumn;
use ZeroToProd\DbModel\Column;
use ZeroToProd\DbModel\ColumnType;

/**
 * A table enum carrying no #[Table] attribute. Every enum under
 * App\Sources\Db does carry one, so this is the only way to reach the branch
 * where HasColumn::table() has no name to read.
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
