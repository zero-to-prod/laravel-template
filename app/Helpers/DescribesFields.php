<?php

namespace App\Helpers;

use ReflectionException;

interface DescribesFields
{
    /** @throws ReflectionException */
    public static function isRequired(string $property): bool;

    /** @throws ReflectionException */
    public static function isSensitive(string $property): bool;

    /** @throws ReflectionException */
    public static function placeholder(string $property): ?string;

    /** @throws ReflectionException */
    public static function legend(string $property): ?string;

    /** @throws ReflectionException */
    public static function icon(string $property): ?string;

    /** @throws ReflectionException */
    public static function description(string $property): ?string;

    /** @throws ReflectionException */
    public static function type(string $property): string;
}
