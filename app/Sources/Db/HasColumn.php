<?php

namespace App\Sources\Db;

use ZeroToProd\DbModel\Column;
use ZeroToProd\DbModel\HasColumnAttribute;
use ZeroToProd\DbModel\PhpType;
use ZeroToProd\SchemaValidator\Property;

trait HasColumn
{
    use HasColumnAttribute;

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
