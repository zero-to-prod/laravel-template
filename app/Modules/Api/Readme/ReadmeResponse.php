<?php

namespace App\Modules\Api\Readme;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class ReadmeResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string content = 'content';

    #[Response([Response::description => 'The API readme, as markdown.'])]
    public string $content;
}
