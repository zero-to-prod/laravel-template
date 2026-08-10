<?php

namespace App\Modules\Api\User\Token;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/**
 * The `{token}` path parameter, shared by the two operations keyed on it.
 *
 * @phpstan-import-type Parameter from ApiSchema
 */
readonly class TokenParameter
{
    public const string name = 'token';

    /**
     * Declared as a string rather than as `PersonalAccessTokens::id->schema()`:
     * the column is a bigint, but a path segment arrives as text, and an
     * `integer` here makes every request to the operation fail parameter
     * validation before the controller is reached.
     *
     * @return Parameter
     */
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
