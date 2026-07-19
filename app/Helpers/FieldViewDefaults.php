<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * Derives field-input Blade props (bag, legend, placeholder, value) from a DataModel class or
 * instance, so `x-field`, `x-text-input`, and `x-textarea-input` don't each re-implement the
 * same `$model ? ... : ...` fallback chain.
 */
class FieldViewDefaults
{
    public static function bag(mixed $model): string
    {
        return $model ? Str::snake(class_basename($model)) : 'default';
    }

    public static function legend(mixed $model, ?string $name): ?string
    {
        return $model && $name ? $model::legend($name) : null;
    }

    public static function placeholder(mixed $model, string $name): ?string
    {
        return $model ? $model::placeholder($name) : null;
    }

    public static function description(mixed $model, ?string $name): ?string
    {
        return $model && $name ? $model::description($name) : null;
    }

    public static function value(mixed $model, string $name): mixed
    {
        return match (true) {
            ! $model => null,
            $model::isSensitive($name) => null,
            default => old($name, is_object($model) ? $model->{$name} : null),
        };
    }
}
