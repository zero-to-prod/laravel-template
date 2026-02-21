<?php

namespace App\Modules\Api;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Endpoint
{
    public function __construct(
        public readonly string $description,
        public readonly array $errors = [],
        public readonly ?string $request = null,
        public readonly ?string $response = null,
        public readonly array $accepts = [],
    ) {}
}