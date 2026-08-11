<?php

namespace App\Modules\Api\Cache;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/**
 * The `{key}` path parameter, shared by the operations keyed on it.
 *
 * @phpstan-import-type Parameter from ApiSchema
 */
readonly class KeyParameter
{
    public const string name = 'key';

    /**
     * Declared as a string rather than as the column schema: a path segment
     * arrives as text, and a narrower type makes every request to the
     * operation fail parameter validation before the controller is reached.
     *
     * @return Parameter
     */
    public static function schema(): array
    {
        return [
            'name' => self::name,
            'in' => 'path',
            'required' => true,
            'description' => 'The cache key.',
            'schema' => [Property::type => Property::string],
        ];
    }
}
