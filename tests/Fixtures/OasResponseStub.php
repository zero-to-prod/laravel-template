<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

/** Exercises every PHP type ResponseSchema maps, plus a described property. */
readonly class OasResponseStub
{
    use DataModel;

    public const string name = 'name';

    #[Describe([Field::field => [Field::description => 'The display name']])]
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

    public ?string $nickname;
}
