<?php

namespace App\Sources\Db\Support;

use Illuminate\Support\Facades\Schema as SchemaFacade;

/**
 * Reads the live schema of the connection's database. This is the source of
 * truth that the PHP artifacts under App\Sources\Db are checked against.
 */
final class DatabaseSchema
{
    /** @return array<string, TableDefinition> */
    public static function read(): array
    {
        $tables = [];

        foreach (SchemaFacade::getTables(SchemaFacade::getConnection()->getDatabaseName()) as $table) {
            $tables[$table['name']] = self::table($table['name'], $table['collation'] ?? '');
        }

        ksort($tables);

        return $tables;
    }

    private static function table(string $name, string $collate): TableDefinition
    {
        $primary = [];
        $unique = [];
        $indexes = [];

        // The primary key and single column unique keys are carried by the
        // column itself, so they are not repeated on the table.
        foreach (SchemaFacade::getIndexes($name) as $index) {
            if ($index['primary']) {
                $primary = $index['columns'];
            } elseif ($index['unique'] && count($index['columns']) === 1) {
                $unique[] = $index['columns'][0];
            } else {
                $indexes[$index['name']] = $index['columns'];
            }
        }

        ksort($indexes);

        $columns = [];

        foreach (SchemaFacade::getColumns($name) as $column) {
            $columns[$column['name']] = new ColumnDefinition(
                name: $column['name'],
                type: $column['type_name'],
                length: self::length($column['type_name'], $column['type']),
                comment: ($column['comment'] ?? '') === '' ? null : $column['comment'],
                nullable: $column['nullable'],
                unique: in_array($column['name'], $unique, true),
                primary_key: in_array($column['name'], $primary, true),
                auto_increment: $column['auto_increment'],
            );
        }

        return new TableDefinition($name, $collate, $columns, $indexes);
    }

    /**
     * Only the character types carry a length that is not implied by the type
     * itself, so an `int(10)` display width is deliberately ignored.
     */
    private static function length(string $type_name, string $type): ?int
    {
        return in_array($type_name, [ColumnType::varchar->value, ColumnType::char->value], true)
            && preg_match('/\((\d+)\)/', $type, $matches) === 1
                ? (int) $matches[1]
                : null;
    }
}
