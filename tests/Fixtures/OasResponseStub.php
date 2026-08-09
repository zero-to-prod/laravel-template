<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

/**
 * Exercises every PHP type ResponseSchema maps, plus a property whose
 * description is resolved from a callable rather than a literal.
 */
readonly class OasResponseStub
{
    use DataModel;

    public const string name = 'name';

    #[Describe([
        Field::field => [
            Field::description => [self::class, 'nameDescription'],
        ],
    ])]
    public string $name;

    public static function nameDescription(): string
    {
        return 'The display name';
    }

    public const string count = 'count';

    public int $count;

    public const string ratio = 'ratio';

    public float $ratio;

    public const string active = 'active';

    public bool $active;

    /** @var array<array-key, mixed> */
    public array $tags;

    public const string nickname = 'nickname';

    /** Described, but without field metadata, so it contributes no description. */
    #[Describe([Describe::nullable => true])]
    public ?string $nickname;
}
