<?php

namespace App\Sources\Db\Support;

use ReflectionEnum;
use ZeroToProd\SchemaValidator\Property;

trait HasColumnAttribute
{
    /** @param  list<mixed>  $arguments */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->attribute($method);
    }

    public function attribute(string $attribute): mixed
    {
        return $this->arguments()[$attribute] ?? null;
    }

    /** @return array<string, mixed> */
    public function schema(): array
    {
        $arguments = $this->arguments();
        $type = $arguments[Column::type] ?? null;
        $schema = ColumnType::from(is_string($type) ? $type : '')->oas();
        $length = $arguments[Column::length] ?? null;
        $comment = $arguments[Column::comment] ?? null;

        if ($schema[Property::type] === Property::string && is_int($length)) {
            $schema[Property::maxLength] = $length;
        }

        if (is_string($comment)) {
            $schema[Property::description] = $comment;
        }

        if (($arguments[Column::nullable] ?? false) === true) {
            $schema[Property::nullable] = true;
        }

        return $schema;
    }

    /** @return list<string> */
    public function rules(): array
    {
        $arguments = $this->arguments();
        $type = $arguments[Column::type] ?? null;
        $rule = ColumnType::from(is_string($type) ? $type : '')->rule();
        $length = $arguments[Column::length] ?? null;

        $rules = [
            ($arguments[Column::nullable] ?? false) === true ? 'nullable' : 'required',
            $rule,
        ];

        if ($rule === 'string' && is_int($length)) {
            $rules[] = 'max:'.$length;
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    private function arguments(): array
    {
        $arguments = new ReflectionEnum(self::class)
            ->getCase($this->name)
            ->getAttributes(Column::class)[0]
            ->getArguments()[0];

        return is_array($arguments) ? $arguments : [];
    }
}
