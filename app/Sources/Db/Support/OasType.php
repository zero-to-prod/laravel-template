<?php

namespace App\Sources\Db\Support;

use Attribute;

/**
 * The OpenAPI type a database column type maps to.
 *
 * Enum cases are class constants, so this targets TARGET_CLASS_CONSTANT.
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class OasType
{
    public function __construct(
        public string $type,
        public ?string $format = null,
    ) {}
}
