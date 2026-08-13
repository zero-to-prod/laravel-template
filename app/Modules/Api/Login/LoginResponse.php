<?php

namespace App\Modules\Api\Login;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class LoginResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string token = 'token';

    #[Response([Response::description => 'API authentication token'])]
    public string $token;
}
