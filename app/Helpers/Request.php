<?php

namespace App\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Request
{
    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}

    /**
     * Validation rules: a pipe delimited string, a list of Rule cases or
     * strings, or a callable returning either.
     */
    public const string rules = 'rules';

    /** Validation messages keyed by rule name. */
    public const string messages = 'messages';

    /** The name this property is called by in validation messages. */
    public const string attributes = 'attributes';
}
