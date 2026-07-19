<?php

namespace App\Modules\Api\Support;

use Attribute;
use Illuminate\Http\JsonResponse;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Endpoint
{
    public const string json = 'application/json';
    public const array content_types = [
        JsonResponse::class => self::json,
    ];

    public function __construct(
        public string $description,
        public array $errors = [],
        public ?string $request_schema = null,
        public ?string $response_schema = null,
        public array $accepts = [],
    ) {}
}
