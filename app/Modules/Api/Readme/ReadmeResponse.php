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

    // No column backs this: the readme is authored markdown served from resources,
    // so the description is all the schema can say about it.
    #[Response([Response::description => 'The API readme, as markdown.'])]
    public string $content;
}
