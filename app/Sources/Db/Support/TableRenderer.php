<?php

namespace App\Sources\Db\Support;

use RuntimeException;

/** Renders a table definition as the PHP enum that mirrors it. */
final readonly class TableRenderer
{
    public function __construct(private SourceSchema $SourceSchema) {}

    public function render(TableDefinition $TableDefinition): string
    {
        $Collation = Collation::tryFrom($TableDefinition->collate)
            ?? throw new RuntimeException("Unsupported collation [{$TableDefinition->collate}]. Add it to ".Collation::class.'.');

        $lines = [
            '<?php',
            '',
            'namespace '.$this->SourceSchema->namespace.';',
            '',
            'use '.Collation::class.';',
            'use '.Column::class.';',
            'use '.ColumnType::class.';',
            'use '.HasColumnAttribute::class.';',
            'use '.Table::class.';',
            '',
            '/**',
            ' * Column attributes are read by name through HasColumnAttribute::__call(),',
            ' * which returns null for any key the column does not declare.',
            ' *',
            ' * @method string type()',
            ' * @method string|null comment()',
            ' * @method int|null length()',
            ' * @method bool|null nullable()',
            ' * @method bool|null unique()',
            ' * @method bool|null primary_key()',
            ' * @method bool|null auto_increment()',
            ' */',
            '#[Table(',
            '    schema: '.class_basename($this->SourceSchema->schema).'::class,',
            '    attributes: [',
            '        Table::name => '.var_export($TableDefinition->name, true).',',
            '        Table::collate => Collation::'.$Collation->name.'->value,',
            ...$this->indexes($TableDefinition),
            '    ])]',
            'enum '.$this->SourceSchema->className($TableDefinition->name).': string',
            '{',
            '    use HasColumnAttribute;',
        ];

        foreach ($TableDefinition->columns as $ColumnDefinition) {
            $lines = [...$lines, '', ...$this->column($ColumnDefinition)];
        }

        return implode("\n", [...$lines, '}', '']);
    }

    /** @return list<string> */
    private function indexes(TableDefinition $TableDefinition): array
    {
        if ($TableDefinition->indexes === []) {
            return [];
        }

        $lines = ['        Table::indexes => ['];

        foreach ($TableDefinition->indexes as $index => $columns) {
            $lines[] = '            '.var_export($index, true).' => [';

            foreach ($columns as $column) {
                $lines[] = "                self::{$column},";
            }

            $lines[] = '            ],';
        }

        return [...$lines, '        ],'];
    }

    /** @return list<string> */
    private function column(ColumnDefinition $ColumnDefinition): array
    {
        $ColumnType = ColumnType::tryFrom($ColumnDefinition->type)
            ?? throw new RuntimeException("Unsupported column type [{$ColumnDefinition->type}]. Add it to ".ColumnType::class.'.');

        $lines = ['    #[Column([', "        Column::name => self::{$ColumnDefinition->name},"];

        if ($ColumnDefinition->comment !== null) {
            $lines[] = '        Column::comment => '.var_export($ColumnDefinition->comment, true).',';
        }

        $lines[] = '        Column::type => ColumnType::'.$ColumnType->name.'->value,';

        if ($ColumnDefinition->length !== null) {
            $lines[] = "        Column::length => {$ColumnDefinition->length},";
        }

        $lines[] = '        Column::nullable => '.var_export($ColumnDefinition->nullable, true).',';

        if ($ColumnDefinition->unique) {
            $lines[] = '        Column::unique => true,';
        }

        if ($ColumnDefinition->primary_key) {
            $lines[] = '        Column::primary_key => true,';
        }

        if ($ColumnDefinition->auto_increment) {
            $lines[] = '        Column::auto_increment => true,';
        }

        return [...$lines, '    ])]', "    case {$ColumnDefinition->name} = ".var_export($ColumnDefinition->name, true).';'];
    }
}
