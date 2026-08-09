<?php

namespace App\DataModels\Fields;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Request
{
    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}

    /** An OAS Schema Object, or a closure returning one. Used verbatim. */
    public const string schema = 'schema';

    /** Hoisted into the parent object's `required` list. */
    public const string required = 'required';

    /**
     * ValueCheck instances for what OAS cannot express: uniqueness, existence,
     * cross-field comparison. Run only after the schema checks pass.
     */
    public const string checks = 'checks';

    /** Overrides `schema.description`. String or closure. */
    public const string description = 'description';
}
