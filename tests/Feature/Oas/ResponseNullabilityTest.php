<?php

use App\Modules\Api\Authenticated\AuthenticatedResponse;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\User\Show\UserShowResponse;

// `HasResponseSchema` publishes every declared field as required, so a nullable
// one is `required` plus `nullable: true`: always present, sometimes null.
//
// `DataModel::from()` does not cooperate by default. It reaches a property
// through `isset($context[$key])`, which is false for a key that is absent and
// false for a key that is present and null, so a nullable property is left
// uninitialized either way. `get_object_vars()` then skips it and the field
// never reaches the body. `#[Describe([Describe::nullable => true])]` on the
// class is what turns both cases into an assignment.
//
// Neither validator catches the gap on its own: the response is only wrong on
// the runs where that field happens to be null, so it survives until a test
// produces one. This asks the question of every model directly instead.
test('a response model initializes every nullable property, so the field is published as null', function (): void {
    $offenders = [];

    foreach (responseModels() as $class) {
        $Properties = new ReflectionClass($class)->getProperties(ReflectionProperty::IS_PUBLIC);

        $nullable = array_values(array_filter(
            $Properties,
            static fn (ReflectionProperty $Property): bool => $Property->getType()?->allowsNull() ?? false,
        ));

        if ($nullable === []) {
            continue;
        }

        // Only the fields a real payload always carries. The nullable ones are
        // left out on purpose: absent is the case that has to become null.
        $payload = [];

        foreach ($Properties as $Property) {
            if (! ($Property->getType()?->allowsNull() ?? false)) {
                $payload[$Property->getName()] = placeholderFor($Property);
            }
        }

        $initialized = get_object_vars($class::from($payload));

        foreach ($nullable as $Property) {
            if (! array_key_exists($Property->getName(), $initialized)) {
                $offenders[] = $class.'::$'.$Property->getName();
            }
        }
    }

    expect($offenders)->toBeEmpty(
        "Declared nullable, so the schema publishes the field as required and nullable, but left\n".
        "uninitialized, so the body omits it. Add #[Describe([Describe::nullable => true])] to the class:\n  - ".
        implode("\n  - ", $offenders)
    );
});

test('the walk reaches the models, rather than passing over an empty list', function (): void {
    expect(responseModels())
        ->toContain(UserShowResponse::class)
        ->toContain(PaginationResponse::class)
        // One with no properties at all, which the walk still has to see.
        ->toContain(AuthenticatedResponse::class);
});

/**
 * Every class under `app/Modules/Api` that publishes a response envelope.
 *
 * Found by the trait rather than by a name, so a model that follows the naming
 * convention and a model that does not are both held to this.
 *
 * @return list<class-string>
 */
function responseModels(): array
{
    $base = app_path('Modules/Api');
    $models = [];

    $Directory = new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS);

    foreach (new RecursiveIteratorIterator($Directory) as $File) {
        if (! $File instanceof SplFileInfo || $File->getExtension() !== 'php') {
            continue;
        }

        /** @var class-string $class */
        $class = 'App\\Modules\\Api'.str_replace('/', '\\', substr($File->getPathname(), strlen($base), -4));

        if (in_array(HasResponseSchema::class, class_uses_recursive($class), true)) {
            $models[] = $class;
        }
    }

    sort($models);

    return $models;
}

/** A value of the property's own type, so `from()` has something it will accept. */
function placeholderFor(ReflectionProperty $ReflectionProperty): mixed
{
    $Type = $ReflectionProperty->getType();

    return match ($Type instanceof ReflectionNamedType ? $Type->getName() : '') {
        'int' => 0,
        'float' => 0.0,
        'bool' => false,
        'array' => [],
        default => '',
    };
}
