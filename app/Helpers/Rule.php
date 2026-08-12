<?php

namespace App\Helpers;

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

    public static function max(int $length): string
    {
        return self::max->value.':'.$length;
    }

    public static function unique(string $table, ?string $column = null): string
    {
        return self::unique->value.':'.$table.($column !== null ? ','.$column : '');
    }
}
