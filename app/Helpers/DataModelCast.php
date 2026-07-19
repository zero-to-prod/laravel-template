<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class DataModelCast
{
    public static function sanitize(?string $value): string
    {
        return Str::squish((string) $value);
    }

    public static function sanitizeNullable(?string $value): ?string
    {
        return Str::squish((string) $value) ?: null;
    }

    public static function sanitizeEmail(?string $value): string
    {
        return Str::squish(strtolower((string) $value));
    }

    public static function toIntNullable(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
