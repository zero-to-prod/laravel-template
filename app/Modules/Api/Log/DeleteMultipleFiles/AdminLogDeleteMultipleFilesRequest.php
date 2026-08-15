<?php

namespace App\Modules\Api\Log\DeleteMultipleFiles;

use App\Helpers\DataModel;
use App\Helpers\Request;
use App\Modules\Api\Support\HasRequestSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class AdminLogDeleteMultipleFilesRequest
{
    use DataModel;
    use HasRequestSchema;

    public const string files = 'files';

    /** @var list<string> */
    #[Request([
        Request::schema => [
            Schema::type => Schema::array,
            Schema::items => [Property::type => Property::string],
            Property::description => 'Encoded log file identifiers.',
        ],
        Request::required => false,
    ])]
    public array $files;
}
