<?php

namespace App\Modules\Api\Support;

use Attribute;

/**
 * What one property of a response body is, declared as a single bag beside it.
 *
 * Every public property is published as present, so whether a value may be null
 * is the php type's to say and never the attribute's. A property that declares no
 * schema is typed from that php type instead, so the attribute is only needed
 * where the type is not the whole answer. A closure defers reading the database.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Response
{
    public const string description = 'description';
    public const string schema = 'schema';

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}
}
