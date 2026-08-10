<?php

namespace App\Sources\Db\Support;

use App\Helpers\DataModel;
use Attribute;

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

    public const string auto_increment = 'auto_increment';
    public const string unique = 'unique';
    public const string comment = 'comment';
    public const string collate = 'collate';
}
