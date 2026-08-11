<?php

namespace App\Modules\Api\Support;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**  @phpstan-import-type OpenApiSchema from ApiSchema */
trait HasResponseSchema
{
    /**
     * @return OpenApiSchema
     *
     * @throws ReflectionException
     */
    public static function schema(): array
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

        $data = self::data();

        if ($data !== []) {
            $properties[ApiResponse::data] = ['schema' => $data, 'required' => true];
        }

        $properties[ApiResponse::type] = [
            'schema' => [Property::type => Property::string, Property::enum => [class_basename(static::class)]],
            'required' => true,
        ];

        return ObjectSchema::make($properties);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public static function data(): array
    {
        $properties = [];

        foreach (new ReflectionClass(static::class)->getProperties(ReflectionProperty::IS_PUBLIC) as $Property) {
            $properties[$Property->getName()] = [
                'schema' => self::property($Property),
                'required' => true,
            ];
        }

        return $properties === [] ? [] : ObjectSchema::make($properties);
    }

    /** @return array<string, mixed> */
    private static function property(ReflectionProperty $ReflectionProperty): array
    {
        $Type = $ReflectionProperty->getType();

        $schema = self::declared($ReflectionProperty) ?? self::fromType($Type);

        $description = self::description($ReflectionProperty);

        if ($description !== null) {
            $schema[Property::description] = $description;
        }

        if ($Type?->allowsNull() ?? true) {
            $schema[Property::nullable] = true;
        }

        return $schema;
    }

    /** @return array<string, mixed> */
    private static function fromType(?ReflectionType $Type): array
    {
        return [
            Property::type => match ($Type instanceof ReflectionNamedType ? $Type->getName() : '') {
                'int' => Property::integer,
                'float' => Property::number,
                'bool' => Property::boolean,
                'array' => Schema::array,
                default => Property::string,
            },
        ];
    }

    /** @return array<string, mixed>|null */
    private static function declared(ReflectionProperty $ReflectionProperty): ?array
    {
        $attributes = $ReflectionProperty->getAttributes(Response::class);

        if ($attributes === []) {
            return null;
        }

        $schema = $attributes[0]->newInstance()->attributes[Response::schema] ?? null;
        $schema = $schema instanceof Closure ? $schema() : $schema;

        if (! is_array($schema) || $schema === []) {
            return null;
        }

        /** @var array<string, mixed> $schema */
        return $schema;
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
