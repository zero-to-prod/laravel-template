<?php

namespace App\Modules\Api\Log\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

#[Describe([Describe::nullable => true])]
readonly class AdminLogIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string file = 'file';

    /** @var array<string, mixed>|null */
    #[Response([Response::schema => [Schema::type => Schema::object]])]
    public ?array $file;

    public const string level_counts = 'level_counts';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Schema::object]]])]
    public array $level_counts;

    public const string logs = 'logs';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Schema::object]]])]
    public array $logs;

    public const string columns = 'columns';

    /** @var list<string>|null */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Property::string]]])]
    public ?array $columns;

    public const string pagination = 'pagination';

    /** @var array<string, mixed>|null */
    #[Response([Response::schema => [Schema::type => Schema::object]])]
    public ?array $pagination;

    public const string expand_automatically = 'expand_automatically';

    #[Response([Response::description => 'expand_automatically'])]
    public bool $expand_automatically;

    public const string cache_recently_cleared = 'cache_recently_cleared';

    #[Response([Response::description => 'cache_recently_cleared'])]
    public bool $cache_recently_cleared;

    public const string has_more_results = 'has_more_results';

    #[Response([Response::description => 'has_more_results'])]
    public bool $has_more_results;

    public const string percent_scanned = 'percent_scanned';

    #[Response([Response::description => 'percent_scanned'])]
    public int $percent_scanned;

    public const string performance = 'performance';

    /** @var array<string, mixed> */
    #[Response([Response::schema => [Schema::type => Schema::object]])]
    public array $performance;
}
