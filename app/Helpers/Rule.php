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
    case alpha_dash = 'alpha_dash';
    case max = 'max';
    case in = 'in';
    case unique = 'unique';
    case exists = 'exists';
    case regex = 'regex';
    case required_if = 'required_if';
    case integer = 'integer';
    case min = 'min';

    public static function max(int $length): string
    {
        return self::max->value.':'.$length;
    }

    public static function min(int $value): string
    {
        return self::min->value.':'.$value;
    }

    public static function requiredIf(string $field, string ...$values): string
    {
        return self::required_if->value.':'.$field.','.implode(',', $values);
    }

    public static function regex(string $pattern): string
    {
        return self::regex->value.':'.$pattern;
    }

    public static function in(string ...$values): string
    {
        return self::in->value.':'.implode(',', $values);
    }

    public static function email(string ...$validations): string
    {
        return self::email->value.':'.implode(',', $validations);

    }

    public static function unique(string $table, ?string $column = null): string
    {
        return self::unique->value.':'.$table.($column !== null ? ','.$column : '');
    }

    public static function exists(string $table, ?string $column = null): string
    {
        return self::exists->value.':'.$table.($column !== null ? ','.$column : '');
    }
}
