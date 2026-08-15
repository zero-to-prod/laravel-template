<?php

namespace App\Modules\Api\Log\Host\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class AdminLogHostIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string hosts = 'hosts';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Schema::object]], Response::description => 'Configured log hosts.'])]
    public array $hosts;
}
