<?php

namespace App\Modules\Api\Support;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Response
{
    public const string description = 'description';

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}
}
