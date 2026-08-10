<?php

namespace App\Sources\Db\Support;

use BackedEnum;

/**
 * A single column, normalized so a definition read from the database and one
 * read from a PHP enum can be compared attribute for attribute.
 */
final readonly class ColumnDefinition
{
    public function __construct(
        public string $name,
        public string $type,
        public ?int $length = null,
        public ?string $comment = null,
        public bool $nullable = false,
        public bool $unique = false,
        public bool $primary_key = false,
        public bool $auto_increment = false,
    ) {}

    /** @param  array<string, mixed>  $attributes */
    public static function fromAttributes(array $attributes): self
    {
        $name = $attributes[Column::name] ?? null;
        $type = $attributes[Column::type] ?? null;
        $length = $attributes[Column::length] ?? null;
        $comment = $attributes[Column::comment] ?? null;

        return new self(
            name: $name instanceof BackedEnum ? (string) $name->value : '',
            type: is_string($type) ? $type : '',
            length: is_int($length) ? $length : null,
            comment: is_string($comment) ? $comment : null,
            nullable: ($attributes[Column::nullable] ?? null) === true,
            unique: ($attributes[Column::unique] ?? null) === true,
            primary_key: ($attributes[Column::primary_key] ?? null) === true,
            auto_increment: ($attributes[Column::auto_increment] ?? null) === true,
        );
    }

    /**
     * Attributes that carry no information are dropped so that an omitted
     * `nullable` and an explicit `nullable => false` compare as equal.
     *
     * @return array<string, string|int|bool>
     */
    public function toArray(): array
    {
        return array_filter([
            Column::type => $this->type,
            Column::length => $this->length,
            Column::comment => $this->comment,
            Column::nullable => $this->nullable,
            Column::unique => $this->unique,
            Column::primary_key => $this->primary_key,
            Column::auto_increment => $this->auto_increment,
        ], static fn (string|int|bool|null $value): bool => $value !== null && $value !== false);
    }
}
