<?php

namespace App\Modules\Api\Log\Folder;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/**
 * The `{folder_identifier}` path parameter, shared by the operations keyed on it.
 *
 * @phpstan-import-type Parameter from ApiSchema
 */
readonly class FolderIdentifierParameter
{
    public const string name = 'folder_identifier';

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
            'description' => 'The encoded log folder identifier.',
            'schema' => [Property::type => Property::string],
        ];
    }
}
