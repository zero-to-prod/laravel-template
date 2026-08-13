<?php

namespace App\Modules\Api\Cache;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/** @phpstan-import-type Parameter from ApiSchema */
readonly class KeyParameter
{
    public const string name = 'key';

    /** @return Parameter */
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
