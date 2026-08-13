<?php

namespace App\Modules\Api\User\Token;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/** @phpstan-import-type Parameter from ApiSchema */
readonly class TokenParameter
{
    public const string name = 'token';

    /** @return Parameter */
    public static function schema(): array
    {
        return [
            'name' => self::name,
            'in' => 'path',
            'required' => true,
            'description' => 'The id of the token.',
            'schema' => [Property::type => Property::string],
        ];
    }
}
