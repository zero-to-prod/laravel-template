<?php

namespace App\Helpers;

/**
 * The validation rules a request adds on top of what its columns already say.
 *
 * A column is the source of truth for what the database enforces — presence, type,
 * length — and states those rules itself. A case here is the request's own
 * addition: uniqueness, confirmation, a format the storage does not care about. A
 * rule taking an argument gets a static builder that assembles it, so no caller
 * concatenates one; a builder named after a case reads the case for its own name.
 */
enum Rule: string
{
    case required = 'required';
    case nullable = 'nullable';
    case string = 'string';
    case boolean = 'boolean';
    case url = 'url';
    case email = 'email';
    case json = 'json';
    case ulid = 'ulid';
    case confirmed = 'confirmed';
    case current_password = 'current_password';
    case alpha_dash = 'alpha_dash';
    case max = 'max';
    case unique = 'unique';
    case integer = 'integer';
    case date = 'date';
    case after = 'after';

    public static function after(string $date): string
    {
        return self::after->value.':'.$date;
    }

    public static function max(int $length): string
    {
        return self::max->value.':'.$length;
    }

    public static function unique(string $table, ?string $column = null): string
    {
        return self::unique->value.':'.$table.($column !== null ? ','.$column : '');
    }
}
