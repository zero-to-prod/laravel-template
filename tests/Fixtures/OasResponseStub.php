<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Modules\Api\Support\Response;
use Zerotoprod\DataModel\Describe;

readonly class OasResponseStub
{
    use DataModel;

    public const string name = 'name';

    #[Response([
        Response::description => static function (): string {
            return 'The display name';
        },
    ])]
    public string $name;

    public const string count = 'count';

    public int $count;

    public const string ratio = 'ratio';

    public float $ratio;

    public const string active = 'active';

    public bool $active;

    /** @var array<array-key, mixed> */
    public array $tags;

    public const string nickname = 'nickname';

    /** Described, but carries no Response attribute, so it contributes no description. */
    #[Describe([Describe::nullable => true])]
    public ?string $nickname;
}
