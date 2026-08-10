<?php

namespace App\Modules\Api\Support;

use App\Helpers\Oas\ObjectSchema;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**  @phpstan-import-type OpenApiSchema from ApiSchema */
readonly class ResponseSchema
{
    /**
     * @param  class-string  $model
     * @return OpenApiSchema
     *
     * @throws ReflectionException
     */
    public static function ok(string $model): array
    {
        $properties = [
            ApiResponse::success => [
                'schema' => [Property::type => Property::boolean, Property::enum => [true]],
                'required' => true,
            ],
            ApiResponse::message => [
                'schema' => [Property::type => Property::string],
                'required' => true,
            ],
        ];

        $data = self::data($model);

        if ($data !== []) {
            $properties[ApiResponse::data] = ['schema' => $data, 'required' => true];
        }

        $properties[ApiResponse::type] = [
            'schema' => [Property::type => Property::string, Property::enum => [class_basename($model)]],
            'required' => true,
        ];

        return ObjectSchema::make($properties);
    }

    /**
     * @param  class-string  $model
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    private static function data(string $model): array
    {
        $properties = [];

        foreach (new ReflectionClass($model)->getProperties(ReflectionProperty::IS_PUBLIC) as $Property) {
            $properties[$Property->getName()] = [
                'schema' => self::property($Property),
                'required' => ! ($Property->getType()?->allowsNull() ?? true),
            ];
        }

        return $properties === [] ? [] : ObjectSchema::make($properties);
    }

    /** @return array<string, mixed> */
    private static function property(ReflectionProperty $ReflectionProperty): array
    {
        $Type = $ReflectionProperty->getType();

        $schema = [
            Property::type => match ($Type instanceof ReflectionNamedType ? $Type->getName() : '') {
                'int' => Property::integer,
                'float' => Property::number,
                'bool' => Property::boolean,
                'array' => Schema::array,
                default => Property::string,
            },
        ];

        $description = self::description($ReflectionProperty);

        return $description === null ? $schema : [...$schema, Property::description => $description];
    }

    private static function description(ReflectionProperty $ReflectionProperty): ?string
    {
        $attributes = $ReflectionProperty->getAttributes(Response::class);

        if ($attributes === []) {
            return null;
        }

        $description = $attributes[0]->newInstance()->attributes[Response::description] ?? null;
        $description = $description instanceof Closure ? $description() : $description;

        return is_string($description) && $description !== '' ? $description : null;
    }
}
