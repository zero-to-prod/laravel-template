<?php

namespace App\Helpers;

use App\Modules\Api\Support\Field;
use ReflectionClass;

trait HasFieldRules
{
    public function rules(): array
    {
        $rules = [];

        foreach (new ReflectionClass($this)->getProperties() as $property) {
            $attributes = $property->getAttributes(Field::class);
            if (empty($attributes)) {
                continue;
            }

            $field = $attributes[0]->newInstance();
            if ($field->rules !== '') {
                $rules[$property->getName()] = $field->rules;
            }
        }

        return $rules;
    }
}