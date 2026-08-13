<?php

namespace App\Sources\Db;

use ReflectionClass;
use ZeroToProd\DbModel\Column;
use ZeroToProd\DbModel\HasColumnAttribute;
use ZeroToProd\DbModel\PhpType;
use ZeroToProd\DbModel\Table;
use ZeroToProd\SchemaValidator\Property;

trait HasColumn
{
    use HasColumnAttribute;

    public static function table(): string
    {
        $attributes = new ReflectionClass(static::class)->getAttributes(Table::class);

        $name = $attributes === [] ? null : ($attributes[0]->newInstance()->attributes[Table::name] ?? null);

        return is_string($name) ? $name : '';
    }

    /** @return array<string, mixed> */
    public function schema(): array
    {
        $arguments = $this->arguments();
        $php = $this->columnType()->php();
        $length = $arguments[Column::length] ?? null;
        $comment = $arguments[Column::comment] ?? null;

        $schema = match ($php) {
            PhpType::int => [Property::type => Property::integer],
            PhpType::DateTimeInterface => [Property::type => Property::string, Property::format => Property::date_time],
            default => [Property::type => Property::string],
        };

        if ($php === PhpType::string && is_int($length)) {
            $schema[Property::maxLength] = $length;
        }

        if (is_string($comment)) {
            $schema[Property::description] = $comment;
        }

        if (($arguments[Column::nullable] ?? false) === true) {
            $schema[Property::nullable] = true;
        }

        return $schema;
    }
}
