<?php

namespace App\Helpers\Oas;

interface ValueCheck
{
    /**
     * @param  array<string, mixed>  $data  The full payload, for cross-field checks.
     */
    public function violation(mixed $value, string $path, array $data): ?Violation;
}
