<?php

namespace App\Mcp\Endpoint;

use Illuminate\Support\Str;

readonly class EndpointParameter
{
    private function __construct(
        public string $name,
        public string $description,
        public ?string $class,
    ) {}

    /** @param  array<string, mixed>  $parameter */
    public static function from(array $parameter): self
    {
        $name = $parameter['name'] ?? null;
        $description = $parameter['description'] ?? null;
        $class = $parameter['class'] ?? null;

        return new self(
            name: is_string($name) ? $name : '',
            description: is_string($description) ? $description : '',
            class: is_string($class) && $class !== '' ? $class : null,
        );
    }

    public function className(): string
    {
        return $this->class !== null ? class_basename($this->class) : Str::studly($this->name).'Parameter';
    }
}
