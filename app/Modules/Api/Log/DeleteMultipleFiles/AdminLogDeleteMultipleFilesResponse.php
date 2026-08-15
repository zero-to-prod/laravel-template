<?php

namespace App\Modules\Api\Log\DeleteMultipleFiles;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class AdminLogDeleteMultipleFilesResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string success = 'success';

    #[Response([Response::description => 'success'])]
    public bool $success;
}
