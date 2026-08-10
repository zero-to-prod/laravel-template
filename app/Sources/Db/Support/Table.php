<?php

namespace App\Sources\Db\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public const string name = 'name';
    public const string collate = 'collate';

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public string $schema, public array $attributes = []) {}
}
