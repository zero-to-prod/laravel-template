<?php

namespace App\Helpers;

use App\View\DataModels\TextInput;
use ReflectionClass;
use ReflectionException;

trait HasTextInput
{
    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public static function textInput(string $property): array
    {
        $attributes = new ReflectionClass(static::class)
            ->getProperty($property)
            ->getAttributes(TextInput::class);

        return [
            TextInput::name => $property,
            ...$attributes === [] ? [] : $attributes[0]->newInstance()->attributes,
        ];
    }
}
