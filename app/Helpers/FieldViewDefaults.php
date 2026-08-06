<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use ReflectionException;

/**
 * Derives field-input Blade props (bag, legend, placeholder, value) from a DataModel class or
 * instance, so `x-field`, `x-text-input`, and `x-textarea-input` don't each re-implement the
 * same `$model ? ... : ...` fallback chain.
 */
class FieldViewDefaults
{
    /** @param  DescribesFields|class-string<DescribesFields>|null  $model */
    public static function bag(mixed $model): string
    {
        return $model ? Str::snake(class_basename($model)) : 'default';
    }

    /**
     * @param  DescribesFields|class-string<DescribesFields>|null  $model
     *
     * @throws ReflectionException
     */
    public static function required(mixed $model, ?string $name): bool
    {
        return $model && $name ? $model::isRequired($name) : false;
    }

    /**
     * @param  DescribesFields|class-string<DescribesFields>|null  $model
     *
     * @throws ReflectionException
     */
    public static function legend(mixed $model, ?string $name): ?string
    {
        return $model && $name ? $model::legend($name) : null;
    }

    /**
     * @param  DescribesFields|class-string<DescribesFields>|null  $model
     *
     * @throws ReflectionException
     */
    public static function placeholder(mixed $model, string $name): ?string
    {
        return $model ? $model::placeholder($name) : null;
    }

    /**
     * @param  DescribesFields|class-string<DescribesFields>|null  $model
     *
     * @throws ReflectionException
     */
    public static function icon(mixed $model, string $name): ?string
    {
        return $model ? $model::icon($name) : null;
    }

    /**
     * @param  DescribesFields|class-string<DescribesFields>|null  $model
     *
     * @throws ReflectionException
     */
    public static function description(mixed $model, ?string $name): ?string
    {
        return $model && $name ? $model::description($name) : null;
    }

    /**
     * @param  DescribesFields|class-string<DescribesFields>|null  $model
     *
     * @throws ReflectionException
     */
    public static function value(mixed $model, string $name): mixed
    {
        return match (true) {
            ! $model => null,
            $model::isSensitive($name) => null,
            default => old($name) ?? (is_object($model) ? get_object_vars($model)[$name] ?? null : null),
        };
    }
}
