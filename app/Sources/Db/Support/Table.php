<?php

namespace App\Sources\Db\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public const string name = 'name';
    public const string collate = 'collate';

    /**
     * Indexes span more than one column, so they are declared on the table
     * rather than on a case, as a map of index name to its ordered columns.
     */
    public const string indexes = 'indexes';

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public string $schema, public array $attributes = []) {}
}
