<?php

namespace App\Modules\Api\Log\File\DownloadRequest;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class AdminLogFileDownloadRequestResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string url = 'url';

    #[Response([Response::description => 'url'])]
    public string $url;
}
