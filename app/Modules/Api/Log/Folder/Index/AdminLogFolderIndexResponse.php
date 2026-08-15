<?php

namespace App\Modules\Api\Log\Folder\Index;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class AdminLogFolderIndexResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string folders = 'folders';

    /** @var list<array<string, mixed>> */
    #[Response([Response::schema => [Schema::type => Schema::array, Schema::items => [Property::type => Schema::object]], Response::description => 'Log folders and their files.'])]
    public array $folders;
}
