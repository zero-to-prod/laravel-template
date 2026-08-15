<?php

namespace App\Modules\Api\Log\Folder\Delete;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class AdminLogFolderDeleteResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string success = 'success';

    #[Response([Response::description => 'success'])]
    public bool $success;
}
