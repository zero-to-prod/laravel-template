<?php

namespace App\Modules\Api\Log\Folder\DownloadRequest;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class AdminLogFolderDownloadRequestResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string url = 'url';

    #[Response([Response::description => 'url'])]
    public string $url;
}
