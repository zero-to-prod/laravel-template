<?php

namespace App\Sources\Db\Support;

/**
 * Compares the database against the PHP artifacts. The database always wins:
 * every message is phrased as what the PHP has to become.
 */
final readonly class SchemaDiff
{
    /**
     * @param  array<string, TableDefinition>  $database
     * @param  array<string, TableDefinition>  $source
     */
    public function __construct(private array $database, private array $source) {}

    /** @return list<string> */
    public function differences(): array
    {
        $differences = [];

        foreach (array_keys($this->database) as $table) {
            if (! isset($this->source[$table])) {
                $differences[] = "Table [{$table}] is not declared in PHP.";

                continue;
            }

            $differences = [...$differences, ...$this->table($table)];
        }

        foreach (array_keys($this->source) as $table) {
            if (! isset($this->database[$table])) {
                $differences[] = "Table [{$table}] is declared in PHP but does not exist in the database.";
            }
        }

        return $differences;
    }

    /** @return list<string> */
    private function table(string $table): array
    {
        $Database = $this->database[$table];
        $Source = $this->source[$table];
        $differences = [];

        if ($Database->collate !== $Source->collate) {
            $differences[] = "Table [{$table}] declares collate [{$Source->collate}], expected [{$Database->collate}].";
        }

        if ($Database->indexes !== $Source->indexes) {
            $differences[] = "Table [{$table}] declares indexes ".self::encode($Source->indexes).', expected '.self::encode($Database->indexes).'.';
        }

        foreach ($Database->columns as $column => $ColumnDefinition) {
            if (! isset($Source->columns[$column])) {
                $differences[] = "Column [{$table}.{$column}] is not declared in PHP.";

                continue;
            }

            $expected = $ColumnDefinition->toArray();
            $declared = $Source->columns[$column]->toArray();

            if ($expected !== $declared) {
                $differences[] = "Column [{$table}.{$column}] declares ".self::encode($declared).', expected '.self::encode($expected).'.';
            }
        }

        foreach (array_keys($Source->columns) as $column) {
            if (! isset($Database->columns[$column])) {
                $differences[] = "Column [{$table}.{$column}] is declared in PHP but does not exist in the database.";
            }
        }

        $expected = array_keys($Database->columns);
        $declared = array_keys($Source->columns);

        // Only worth reporting once both sides hold the same set of columns,
        // otherwise the missing and extra columns already say it.
        if ($expected !== $declared && array_diff($expected, $declared) === [] && array_diff($declared, $expected) === []) {
            $differences[] = "Table [{$table}] declares columns in the order ".self::encode($declared).', expected '.self::encode($expected).'.';
        }

        return $differences;
    }

    private static function encode(mixed $value): string
    {
        return (string) json_encode($value);
    }
}
