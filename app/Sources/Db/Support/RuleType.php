<?php

namespace App\Sources\Db\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class RuleType
{
    public function __construct(public string $rule) {}
}
