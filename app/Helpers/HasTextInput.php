<?php

namespace App\Helpers;

use App\View\DataModels\TextInput;
use ReflectionClass;
use ReflectionException;

/** @phpstan-require-implements DescribesFields */
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
            TextInput::required => static::isRequired($property),
            TextInput::title => static::description($property),
            ...$attributes === [] ? [] : $attributes[0]->newInstance()->attributes,
        ];
    }
}
