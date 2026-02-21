<?php

namespace App\Modules\Api\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Endpoint
{
    public function __construct(
        public readonly string $description,
        public readonly array $errors = [],
        public readonly ?string $request_schema = null,
        public readonly ?string $response_schema = null,
        public readonly array $accepts = [],
    ) {}
}