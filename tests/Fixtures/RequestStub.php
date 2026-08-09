<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Helpers\HasRules;
use App\Helpers\Request;
use App\Helpers\Rule;
use Zerotoprod\DataModel\Describe;

readonly class RequestStub
{
    use DataModel;
    use HasRules;

    public const string website = 'website';

    #[Request([
        Request::rules => [Rule::required, Rule::url],
        Request::messages => [Rule::required->value => 'A website is required.'],
        Request::attributes => 'website address',
    ])]
    public string $website;

    public const string secret = 'secret';

    #[Request([Request::rules => 'nullable|string'])]
    public string $secret;

    public const string callable = 'callable';

    #[Request([Request::rules => [self::class, 'callableRules']])]
    public string $callable;

    public const string blank = 'blank';

    #[Request([Request::rules => ''])]
    public string $blank;

    public const string untagged = 'untagged';

    #[Describe([Describe::default => 'untagged'])]
    public string $untagged;

    /** @return list<Rule|string> */
    public static function callableRules(): array
    {
        return [Rule::nullable, Rule::max(10)];
    }

    public static function make(): self
    {
        return self::from([
            self::website => 'https://example.com',
            self::secret => 'shh',
            self::callable => 'value',
            self::blank => 'blank',
        ]);
    }
}
