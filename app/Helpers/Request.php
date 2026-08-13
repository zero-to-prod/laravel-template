<?php

namespace App\Helpers;

use Attribute;

/**
 * What one property of a request is, declared as a single bag beside it.
 *
 * Two readers share the attribute and ignore each other's keys. The api side
 * takes the schema fragment, whether the property is required, and the checks a
 * schema cannot express — one declaration becomes both the published request body
 * and the validator that runs. The web-form side takes rules, messages and the
 * display name for a plain validator. A property declares for one side, not both.
 * A schema may be given as a closure so nothing reads the database at parse time.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Request
{
    public const string schema = 'schema';
    public const string required = 'required';
    public const string checks = 'checks';
    public const string description = 'description';
    public const string attributes = 'attributes';
    public const string rules = 'rules';
    public const string messages = 'messages';

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}
}
