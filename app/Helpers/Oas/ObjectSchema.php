<?php

namespace App\Helpers\Oas;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Schema;

/**
 * @phpstan-import-type OpenApiSchema from ApiSchema
 */
readonly class ObjectSchema
{
    /**
     * Assembles property fragments into an object Schema Object.
     *
     * OAS puts `required` on the parent, so the per-property flag is hoisted
     * here rather than living inside the property's own fragment.
     *
     * @param  array<string, array{schema: array<string, mixed>, required: bool}>  $properties
     * @return OpenApiSchema
     */
    public static function make(array $properties): array
    {
        $required = [];
        $schemas = [];

        foreach ($properties as $name => $property) {
            $schemas[$name] = $property['schema'];

            if ($property['required']) {
                $required[] = $name;
            }
        }

        $schema = [Schema::type => Schema::object];

        if ($required !== []) {
            $schema[Schema::required] = $required;
        }

        if ($schemas !== []) {
            $schema[Schema::properties] = $schemas;
        }

        return $schema;
    }
}
