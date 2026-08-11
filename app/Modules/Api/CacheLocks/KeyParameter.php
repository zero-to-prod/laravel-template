<?php

namespace App\Modules\Api\CacheLocks;

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
            'description' => 'The name of the lock.',
            'schema' => [Property::type => Property::string],
        ];
    }
}
