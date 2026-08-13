<?php

namespace App\Helpers;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use ReflectionClass;

trait IsRequest
{
    /** @return array{array<string, mixed>, array<string, list<string|ValidationRule|Rule>>, array<string, string>, array<string, string>} */
    public function validator(): array
    {
        return [$this->toArray(), $this->rules(), $this->messages(), $this->attributes()];
    }

    /** @return array<string, list<string|ValidationRule|Rule>> */
    public function rules(): array
    {
        $rules = [];

        foreach (self::requests() as $name => $Request) {
            $resolved = self::resolveRules($Request->attributes[Request::rules] ?? '');

            if ($resolved !== []) {
                $rules[$name] = $resolved;
            }
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $messages = [];

        foreach (self::requests() as $name => $Request) {
            $declared = $Request->attributes[Request::messages] ?? [];

            foreach (is_array($declared) ? $declared : [] as $rule => $message) {
                if (is_string($message)) {
                    $messages["$name.$rule"] = $message;
                }
            }
        }

        return $messages;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [];

        foreach (self::requests() as $name => $Request) {
            $attribute = $Request->attributes[Request::attributes] ?? '';

            if (is_string($attribute) && $attribute !== '') {
                $attributes[$name] = $attribute;
            }
        }

        return $attributes;
    }

    /** @return list<string|ValidationRule|Rule> */
    private static function resolveRules(mixed $rules): array
    {
        if ($rules instanceof Closure || (is_array($rules) && is_callable($rules))) {
            $rules = $rules();
        }

        if (is_string($rules)) {
            return $rules === '' ? [] : explode('|', $rules);
        }

        $resolved = [];

        foreach (is_array($rules) ? $rules : [] as $rule) {
            if ($rule instanceof BackedEnum) {
                $resolved[] = (string) $rule->value;
            } elseif (is_string($rule) || $rule instanceof ValidationRule || $rule instanceof Rule) {
                $resolved[] = $rule;
            }
        }

        return $resolved;
    }

    /** @return iterable<string, Request> */
    private static function requests(): iterable
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
