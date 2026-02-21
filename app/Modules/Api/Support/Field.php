<?php

namespace App\Modules\Api\Support;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Field
{
    public function __construct(
        public string $description = '',
        public string $rules = '',
    ) {}
}