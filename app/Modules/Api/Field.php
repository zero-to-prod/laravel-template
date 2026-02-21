<?php

namespace App\Modules\Api;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public readonly string $description = '',
        public readonly string $rules = '',
    ) {}
}