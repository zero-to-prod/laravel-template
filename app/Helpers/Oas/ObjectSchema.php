<?php

namespace App\Helpers\Oas;

use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Schema;

/** @phpstan-import-type OpenApiSchema from ApiSchema */
readonly class ObjectSchema
{
    /**
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
