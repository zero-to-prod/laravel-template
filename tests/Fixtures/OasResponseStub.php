<?php

namespace Tests\Fixtures;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\Users;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;

readonly class OasResponseStub
{
    use DataModel;
    use HasResponseSchema;

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

    public const string verified_at = 'verified_at';

    /** Adopts the column's schema, so it carries a format the php type cannot express. */
    #[Response([
        Response::schema => static function (): array {
            return Users::email_verified_at->schema();
        },
    ])]
    #[Describe([Describe::nullable => true])]
    public ?string $verified_at;

    public const string label = 'label';

    /** A declared schema an explicit description wins over. */
    #[Response([
        Response::schema => [Property::type => Property::string, Property::description => 'The column comment'],
        Response::description => 'The overriding description',
    ])]
    public string $label;

    public const string empty_schema = 'empty_schema';

    /** An empty declared schema falls back to the php type. */
    #[Response([Response::schema => []])]
    public string $empty_schema;

    public const string nickname = 'nickname';

    /** Described, but carries no Response attribute, so it contributes no description. */
    #[Describe([Describe::nullable => true])]
    public ?string $nickname;
}
