<?php

namespace App\Sources\Db\Support;

use BackedEnum;

/**
 * A single table, normalized so a definition read from the database and one
 * read from a PHP enum can be compared.
 *
 * Columns keep their declaration order because that order is itself part of
 * the schema; indexes are sorted by name because the database does not order
 * them meaningfully.
 */
final readonly class TableDefinition
{
    /**
     * @param  array<string, ColumnDefinition>  $columns
     * @param  array<string, list<string>>  $indexes
     */
    public function __construct(
        public string $name,
        public string $collate,
        public array $columns = [],
        public array $indexes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, ColumnDefinition>  $columns
     */
    public static function fromAttributes(array $attributes, array $columns): self
    {
        $name = $attributes[Table::name] ?? null;
        $collate = $attributes[Table::collate] ?? null;
        $declared = $attributes[Table::indexes] ?? [];
        $indexes = [];

        foreach (is_array($declared) ? $declared : [] as $index => $cases) {
            $indexes[(string) $index] = array_values(array_map(
                static fn (mixed $case): string => $case instanceof BackedEnum ? (string) $case->value : '',
                is_array($cases) ? $cases : [],
            ));
        }

        ksort($indexes);

        return new self(
            is_string($name) ? $name : '',
            is_string($collate) ? $collate : '',
            $columns,
            $indexes,
        );
    }
}
