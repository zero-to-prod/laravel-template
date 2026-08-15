<?php

namespace App\Modules\Api\Log\File\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class AdminLogFileIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string files = 'files';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Schema::object]], Response::description => 'Available log files.'])]
    public array $files;
}
