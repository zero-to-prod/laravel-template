<?php

namespace App\Sources\Db\Support;

use App\Helpers\DataModel;
use Attribute;

// Declared on properties (Cache) and on enum cases, which are class constants (Users).
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS_CONSTANT)]
class Column
{
    use DataModel;

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}

    public const string name = 'name';
    public const string type = 'type';
    public const string length = 'length';
    public const string nullable = 'nullable';
    public const string primary_key = 'primary_key';

    public bool $primary_key;

    public const string unique = 'unique';
    public const string comment = 'comment';
    public const string collate = 'collate';
}
