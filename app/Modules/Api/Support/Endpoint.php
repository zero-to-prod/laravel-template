<?php

namespace App\Modules\Api\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Endpoint
{
    public function __construct(
        public string $description,
        public array $errors = [],
        public ?string $request_schema = null,
        public ?string $response_schema = null,
        public array $accepts = [],
    ) {}
}