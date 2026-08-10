<?php

namespace App\Sources\Db\Support;

use Illuminate\Support\Str;
use ReflectionClass;
use RuntimeException;

/**
 * A schema declared in PHP: the enum carrying the #[Schema] attribute plus the
 * directory of table enums that sits beside it.
 */
final readonly class SourceSchema
{
    /** @param  class-string  $schema */
    private function __construct(
        public string $schema,
        public string $namespace,
        public string $directory,
    ) {}

    public static function make(string $name): self
    {
        $namespace = 'App\\Sources\\Db\\'.$name;
        $schema = $namespace.'\\'.$name;

        if (! enum_exists($schema) || new ReflectionClass($schema)->getAttributes(Schema::class) === []) {
            throw new RuntimeException("No enum carrying the #[Schema] attribute was found at [{$schema}].");
        }

        return new self($schema, $namespace, app_path('Sources/Db/'.$name));
    }

    public function className(string $table): string
    {
        return Str::studly($table);
    }

    public function path(string $table): string
    {
        return $this->directory.'/'.$this->className($table).'.php';
    }

    /** @return array<string, TableDefinition> */
    public function tables(): array
    {
        $tables = [];

        foreach (glob($this->directory.'/*.php') ?: [] as $file) {
            $class = $this->namespace.'\\'.basename($file, '.php');

            // The schema enum itself lives in this directory and declares no table.
            if (! class_exists($class) || new ReflectionClass($class)->getAttributes(Table::class) === []) {
                continue;
            }

            $Reflection = new ReflectionClass($class);
            $attributes = $Reflection->getAttributes(Table::class);
            $columns = [];

            // Cases are class constants, so they are invisible to getProperties().
            foreach ($Reflection->getReflectionConstants() as $Constant) {
                foreach ($Constant->getAttributes(Column::class) as $Attribute) {
                    $ColumnDefinition = ColumnDefinition::fromAttributes($Attribute->newInstance()->attributes);
                    $columns[$ColumnDefinition->name] = $ColumnDefinition;
                }
            }

            $TableDefinition = TableDefinition::fromAttributes($attributes[0]->newInstance()->attributes, $columns);
            $tables[$TableDefinition->name] = $TableDefinition;
        }

        ksort($tables);

        return $tables;
    }
}
