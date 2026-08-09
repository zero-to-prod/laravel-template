<?php

namespace App\Helpers\Oas;

readonly class Violation
{
    public function __construct(
        /** Dot path: `email`, `items.0.id`. */
        public string $path,
        public string $keyword,
        public string $message,
    ) {}
}
