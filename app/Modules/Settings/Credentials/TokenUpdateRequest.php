<?php

namespace App\Modules\Settings\Credentials;

use App\Helpers\DataModel;
use App\Modules\Api\Support\AbilityQuery;
use Zerotoprod\DataModel\Describe;

readonly class TokenUpdateRequest
{
    use DataModel;

    public const string abilities = 'abilities';

    /** @var list<string> */
    #[Describe([
        Describe::cast => [self::class, 'granted'],
        Describe::default => [self::class, 'granted'],
    ])]
    public array $abilities;

    /** @return list<string> */
    public static function granted(mixed $value): array
    {
        $submitted = array_filter(is_array($value) ? $value : [], 'is_string');

        return array_values(array_intersect($submitted, AbilityQuery::abilities()));
    }
}
