<?php

namespace App\Mcp\Endpoint;

/**
 * One field of a request or response DTO, as the scaffolder was told about it.
 *
 * A field either names a column enum, in which case the generated attribute
 * adopts that column's schema, or carries a description of its own, which is
 * what a field no column backs gets instead.
 */
readonly class EndpointField
{
    private function __construct(
        public string $name,
        public string $type,
        public bool $nullable,
        public bool $required,
        public ?string $table,
        public string $column,
        public ?string $description,
        public ?string $itemsOf,
    ) {}

    /**
     * @param  array<string, mixed>  $field
     */
    public static function from(array $field): self
    {
        $name = self::text($field, 'name') ?? '';

        return new self(
            name: $name,
            type: self::text($field, 'type') ?? 'string',
            nullable: ($field['nullable'] ?? false) === true,
            required: ($field['required'] ?? false) === true,
            table: self::text($field, 'table'),
            column: self::text($field, 'column') ?? $name,
            description: self::text($field, 'description'),
            itemsOf: self::text($field, 'items_of'),
        );
    }

    /**
     * The property type as it is declared, which is what decides nullability
     * in the published document.
     */
    public function declaredType(): string
    {
        return ($this->nullable ? '?' : '').$this->type;
    }

    /**
     * A value of the right type for a generated test payload. Valid against
     * the type, not necessarily against the field's own constraints.
     */
    public function placeholder(): string
    {
        return match ($this->type) {
            'int' => '1',
            'float' => '1.0',
            'bool' => 'true',
            'array' => '[]',
            default => "'example'",
        };
    }

    /**
     * Whether a blank value of this field reaches the 422: the one invalid
     * body the document still admits is a blank required string.
     */
    public function reachesValidationError(): bool
    {
        return $this->required && ! $this->nullable && $this->type === 'string';
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private static function text(array $field, string $key): ?string
    {
        $value = $field[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
