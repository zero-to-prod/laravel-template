<?php

namespace App\Modules\Api\Admin\LogInvestigation;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

#[Describe([Describe::nullable => true])]
readonly class AdminLogInvestigationResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string summary = 'summary';

    /** @var array<string, mixed> */
    #[Response([Response::schema => [Schema::type => Schema::object]])]
    public array $summary;

    public const string findings = 'findings';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Schema::object]]])]
    public array $findings;

    public const string level_counts = 'level_counts';

    /** @var array<string, int> */
    #[Response([Response::schema => [
        Schema::type => Schema::object,
        Schema::additionalProperties => [Property::type => Property::integer],
    ]])]
    public array $level_counts;

    public const string next_cursor = 'next_cursor';

    public ?string $next_cursor;
}
