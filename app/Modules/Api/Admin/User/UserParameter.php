<?php

namespace App\Modules\Api\Admin\User;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/** @phpstan-import-type Parameter from ApiSchema */
readonly class UserParameter
{
    public const string name = 'user';

    /** @return Parameter */
    public static function schema(): array
    {
        return [
            'name' => self::name,
            'in' => 'path',
            'required' => true,
            'description' => 'The user id.',
            'schema' => [Property::type => Property::string],
        ];
    }
}
