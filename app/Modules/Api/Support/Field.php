<?php

namespace App\Modules\Api\Support;

use App\Helpers\DataModel;
use Closure;
use Zerotoprod\DataModel\Describe;

readonly class Field
{
    use DataModel;

    public const string field = 'field';
    public const string description = 'description';

    /** A literal, or a callable returning one, so a description can come from its column comment. */
    #[Describe([
        Describe::default => '',
        Describe::cast => [self::class, 'resolveDescription'],
    ])]
    public string $description;

    public static function resolveDescription(mixed $value): string
    {
        $description = $value instanceof Closure || (is_array($value) && is_callable($value))
            ? $value()
            : $value;

        return is_string($description) ? $description : '';
    }
}
