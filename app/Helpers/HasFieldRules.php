<?php

namespace App\Helpers;

use App\Modules\Api\Support\Field;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Zerotoprod\DataModel\Describe;

trait HasFieldRules
{
    /**
     * @return array{array<string, mixed>, array<string, list<string>>, array<string, string>, array<string, string>}
     *
     * @throws ReflectionException
     */
    public function validator(): array
    {
        return [$this->toArray(), $this->rules(), $this->messages(), $this->attributes()];
    }

    /** @throws ReflectionException */
    public static function isRequired(string $property): bool
    {
        return in_array(Rule::required->value, self::resolveField($property)?->resolvedRules() ?? [], true);
    }

    /** @throws ReflectionException */
    public static function isSensitive(string $property): bool
    {
        return self::resolveField($property)->sensitive ?? false;
    }

    /** @throws ReflectionException */
    public static function placeholder(string $property): ?string
    {
        return self::resolveField($property)?->placeholder;
    }

    /** @throws ReflectionException */
    public static function legend(string $property): ?string
    {
        return self::resolveField($property)?->legend;
    }

    /** @throws ReflectionException */
    public static function icon(string $property): ?string
    {
        return self::resolveField($property)?->icon;
    }

    /** @throws ReflectionException */
    public static function description(string $property): ?string
    {
        $description = self::resolveField($property)?->description;

        return $description === '' ? null : $description;
    }

    /** @throws ReflectionException */
    public static function type(string $property): string
    {
        $Field = self::resolveField($property);

        if ($Field === null) {
            return 'text';
        }

        if ($Field->sensitive) {
            return 'password';
        }

        $rules = $Field->resolvedRules();

        return match (true) {
            in_array(Rule::url->value, $rules, true) => 'url',
            in_array(Rule::email->value, $rules, true) => 'email',
            default => 'text',
        };
    }

    /** @throws ReflectionException */
    private static function resolveField(string $property): ?Field
    {
        foreach (self::fields() as $name => $Field) {
            if ($name === $property) {
                return $Field;
            }
        }

        return null;
    }

    /**
     * @return array<string, list<string>>
     *
     * @throws ReflectionException
     */
    public function rules(): array
    {
        $rules = [];

        foreach (self::fields() as $name => $Field) {
            $fieldRules = $Field->resolvedRules();
            if ($fieldRules !== []) {
                $rules[$name] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     *
     * @throws ReflectionException
     */
    public function messages(): array
    {
        $messages = [];

        foreach (self::fields() as $name => $Field) {
            foreach ($Field->messages as $rule => $message) {
                $messages["$name.$rule"] = $message;
            }
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     *
     * @throws ReflectionException
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (self::fields() as $name => $Field) {
            if ($Field->attributes !== '') {
                $attributes[$name] = $Field->attributes;
            }
        }

        return $attributes;
    }

    /**
     * @return iterable<string, Field>
     *
     * @throws ReflectionException
     */
    private static function fields(): iterable
    {
        foreach (new ReflectionClass(static::class)->getProperties() as $property) {
            $attributes = $property->getAttributes(Describe::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($attributes === []) {
                continue;
            }

            $field = $attributes[0]->newInstance()->extra[Field::field] ?? null;
            if ($field === null) {
                continue;
            }

            yield $property->getName() => Field::from($field);
        }
    }
}
