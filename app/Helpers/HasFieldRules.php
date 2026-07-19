<?php

namespace App\Helpers;

use App\Modules\Api\Support\Field;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Zerotoprod\DataModel\Describe;

trait HasFieldRules
{
    public function validator(): array
    {
        return [$this->toArray(), $this->rules(), $this->messages(), $this->attributes()];
    }

    /**
     * Whether the given property carries a `required` validation rule, so views can
     * derive HTML `required`/optionality markers from the DataModel instead of hardcoding them.
     *
     * @throws ReflectionException
     */
    public static function isRequired(string $property): bool
    {
        $Field = self::resolveField($property);

        if ($Field === null) {
            return false;
        }

        $rules = $Field->resolvedRules();
        $rules = is_array($rules) ? $rules : explode('|', (string) $rules);

        return in_array(Rule::required->value, $rules, true);
    }

    /**
     * Whether the given property holds a secret (token, password, client secret, etc.) that a view
     * must never echo back and should render as a masked input.
     *
     * @throws ReflectionException
     */
    public static function isSensitive(string $property): bool
    {
        return self::resolveField($property)?->sensitive ?? false;
    }

    /**
     * The placeholder text a view should render for this property, so views can derive it from
     * the DataModel instead of hardcoding it.
     *
     * @throws ReflectionException
     */
    public static function placeholder(string $property): ?string
    {
        return self::resolveField($property)?->placeholder;
    }

    /**
     * The field label a view should render for this property, so views can derive it from
     * the DataModel instead of hardcoding it.
     *
     * @throws ReflectionException
     */
    public static function legend(string $property): ?string
    {
        return self::resolveField($property)?->legend;
    }

    /**
     * The field description a view should render as an input's `title` attribute, so views can
     * derive it from the DataModel instead of hardcoding it.
     *
     * @throws ReflectionException
     */
    public static function description(string $property): ?string
    {
        $description = self::resolveField($property)?->description;

        return $description === '' ? null : $description;
    }

    /**
     * The HTML `type` a view should render for this property, so views can derive it from the
     * DataModel's validation rules and sensitivity instead of hardcoding it.
     *
     * @throws ReflectionException
     */
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
        $rules = is_array($rules) ? $rules : explode('|', (string) $rules);

        return in_array(Rule::url->value, $rules, true) ? 'url' : 'text';
    }

    /** @throws ReflectionException */
    private static function resolveField(string $property): ?Field
    {
        foreach (new static()->fields() as $name => $Field) {
            if ($name === $property) {
                return $Field;
            }
        }

        return null;
    }

    /** @throws ReflectionException */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->fields() as $name => $Field) {
            $fieldRules = $Field->resolvedRules();
            if ($fieldRules !== '' && $fieldRules !== []) {
                $rules[$name] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * @throws ReflectionException
     */
    public function messages(): array
    {
        $messages = [];

        foreach ($this->fields() as $name => $Field) {
            foreach ($Field->messages as $rule => $message) {
                $messages["$name.$rule"] = $message;
            }
        }

        return $messages;
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->fields() as $name => $Field) {
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
    private function fields(): iterable
    {
        foreach (new ReflectionClass($this)->getProperties() as $property) {
            $attributes = $property->getAttributes(Describe::class, ReflectionAttribute::IS_INSTANCEOF);
            if (empty($attributes)) {
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
