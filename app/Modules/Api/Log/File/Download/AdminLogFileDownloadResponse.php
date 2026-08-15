<?php

namespace App\Modules\Api\Log\File\Download;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

readonly class AdminLogFileDownloadResponse
{
    use DataModel;
    use HasResponseSchema;
}
