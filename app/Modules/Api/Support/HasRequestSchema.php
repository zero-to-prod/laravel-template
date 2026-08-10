<?php

namespace App\Modules\Api\Support;

use App\Helpers\Request;
use Closure;
use Illuminate\Validation\Validator;
use ReflectionClass;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\SchemaValidator;

/** @phpstan-import-type OpenApiSchema from ApiSchema */
trait HasRequestSchema
{
    /**
     * @return OpenApiSchema
     *
     * @throws ReflectionException
     */
    public static function schema(): array
    {
        $properties = [];

        foreach (self::requestFields() as $name => $Request) {
            $properties[$name] = [
                'schema' => self::resolveSchema($Request),
                'required' => ($Request->attributes[Request::required] ?? false) === true,
            ];
        }

        return ObjectSchema::make($properties);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ReflectionException
     */
    public static function validator(array $data): Validator
    {
        return SchemaValidator::make($data, self::schema())
            ->after(static function (Validator $Validator) use ($data): void {
                if ($Validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach (self::runChecks(self::from($data)->toArray()) as $Violation) {
                    $Validator->errors()->add($Violation->path, $Violation->message);
                }
            });
    }

    /** @return array<string, mixed> */
    private static function resolveSchema(Request $Request): array
    {
        $schema = $Request->attributes[Request::schema] ?? [];
        $schema = $schema instanceof Closure ? $schema() : $schema;

        if (! is_array($schema)) {
            return [];
        }

        $description = $Request->attributes[Request::description] ?? null;
        $description = $description instanceof Closure ? $description() : $description;

        return is_string($description)
            ? [...$schema, Property::description => $description]
            : $schema;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<Violation>
     *
     * @throws ReflectionException
     */
    public static function runChecks(array $data): array
    {
        $violations = [];

        foreach (self::requestFields() as $name => $Request) {
            $checks = $Request->attributes[Request::checks] ?? [];

            foreach (is_array($checks) ? $checks : [] as $ValueCheck) {
                if (! $ValueCheck instanceof ValueCheck) {
                    continue;
                }

                $Violation = $ValueCheck->violation($data[$name] ?? null, $name, $data);

                if ($Violation instanceof Violation) {
                    $violations[] = $Violation;
                }
            }
        }

        return $violations;
    }

    /**
     * @return iterable<string, Request>
     *
     * @throws ReflectionException
     */
    private static function requestFields(): iterable
    {
        foreach (new ReflectionClass(static::class)->getProperties() as $property) {
            $attributes = $property->getAttributes(Request::class);

            if ($attributes === []) {
                continue;
            }

            yield $property->getName() => $attributes[0]->newInstance();
        }
    }
}
