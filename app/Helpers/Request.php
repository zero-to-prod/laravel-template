<?php

namespace App\Helpers;

use Attribute;

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
