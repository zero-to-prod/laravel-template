<?php

namespace App\Helpers\Oas;

readonly class Violation
{
    public function __construct(public string $path, public string $keyword, public string $message) {}
}
