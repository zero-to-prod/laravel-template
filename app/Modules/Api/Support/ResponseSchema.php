<?php

namespace App\Modules\Api\Support;

use App\Helpers\Oas\ObjectSchema;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**
 * The success envelope, derived from the model the endpoint responds with.
 *
 * Api::resolveType() publishes the payload's class basename as `type` and
 * Api::respond() drops an empty `data`, so both follow from the model rather
 * than being restated next to it.
 *
 * @phpstan-import-type OpenApiSchema from ApiSchema
 */
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
     * A model with no properties serialises to an empty `data`, which
     * Api::respond() strips, so it contributes nothing to the envelope.
     *
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

    /** The same #[Describe] field metadata the form layer reads. */
    private static function description(ReflectionProperty $ReflectionProperty): ?string
    {
        $attributes = $ReflectionProperty->getAttributes(Describe::class, ReflectionAttribute::IS_INSTANCEOF);

        if ($attributes === []) {
            return null;
        }

        $field = $attributes[0]->newInstance()->extra[Field::field] ?? null;

        if (! is_array($field)) {
            return null;
        }

        $description = Field::from($field)->description;

        return $description === '' ? null : $description;
    }
}
