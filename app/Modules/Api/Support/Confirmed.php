<?php

namespace App\Modules\Api\Support;

readonly class Confirmed implements ValueCheck
{
    public function __construct(private string $suffix = '_confirmation') {}

    /** @param  array<string, mixed>  $data */
    public function violation(mixed $value, string $path, array $data): ?Violation
    {
        return ($data[$path.$this->suffix] ?? null) === $value
            ? null
            : new Violation($path, 'confirmed', 'The confirmation does not match.');
    }
}
