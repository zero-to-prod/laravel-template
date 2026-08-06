<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Helpers\DescribesFields;
use App\Helpers\HasFieldRules;
use App\Helpers\Rule;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class FieldStub implements DescribesFields
{
    use DataModel;
    use HasFieldRules;

    public const string website = 'website';

    #[Describe([
        Field::field => [
            Field::description => 'Homepage',
            Field::legend => 'Website',
            Field::placeholder => 'https://example.com',
            Field::rules => [Rule::required, Rule::url],
            Field::messages => [Rule::required->value => 'A website is required.'],
            Field::attributes => 'website address',
        ],
    ])]
    public string $website;

    public const string secret = 'secret';

    #[Describe([
        Field::field => [
            Field::description => 'A secret',
            Field::rules => 'nullable|string',
            Field::sensitive => true,
        ],
    ])]
    public string $secret;

    public const string blank = 'blank';

    #[Describe([
        Field::field => [Field::description => ''],
    ])]
    public string $blank;

    public const string untagged = 'untagged';

    public string $untagged;

    public const string undescribed = 'undescribed';

    #[Describe([Describe::default => 'undescribed'])]
    public string $undescribed;

    public static function make(): self
    {
        return self::from([
            self::website => 'https://example.com',
            self::secret => 'shh',
            self::blank => 'blank',
            self::untagged => 'untagged',
        ]);
    }
}
