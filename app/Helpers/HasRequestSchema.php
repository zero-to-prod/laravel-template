<?php

namespace App\Helpers;

use App\DataModels\Fields\Request;
use App\Helpers\Oas\ObjectSchema;
use App\Helpers\Oas\ValueCheck;
use App\Helpers\Oas\Violation;
use Closure;
use Illuminate\Validation\Validator;
use ReflectionClass;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\SchemaValidator;

/**
 * @phpstan-import-type OpenApiSchema from ApiSchema
 */
trait HasRequestSchema
{
    /**
     * The requestBody Schema Object for this request.
     *
     * @return OpenApiSchema
     *
     * @throws ReflectionException
     */
    public static function rules(): array
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
     * The schema covers what OAS can express; the ValueChecks cover what it
     * cannot.
     *
     * Takes the raw input rather than a hydrated instance. Hydration assigns
     * straight to typed properties, so a non-scalar value is a TypeError before
     * the validator ever sees it, and a cast would have the validator judging a
     * value the client never sent. Validating first makes the runtime enforce
     * the same document that `rules()` publishes, and makes `from()` total for
     * anything that gets past here.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ReflectionException
     */
    public static function validator(array $data): Validator
    {
        return SchemaValidator::make($data, self::rules())
            ->after(static function (Validator $Validator) use ($data): void {
                // A ValueCheck may query the database, so only run one against
                // a payload the schema already accepted. Hydrating is safe by
                // this point, and gives the checks the canonical values the
                // casts produce rather than the raw ones.
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
