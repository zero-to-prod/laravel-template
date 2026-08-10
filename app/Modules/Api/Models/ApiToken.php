<?php

namespace App\Modules\Api\Models;

use App\Helpers\DataModel;
use App\Modules\Api\Support\Response;

readonly class ApiToken
{
    use DataModel;

    public const string token = 'token';

    #[Response([Response::description => 'API authentication token'])]
    public string $token;
}
