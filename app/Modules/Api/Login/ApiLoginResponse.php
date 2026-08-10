<?php

namespace App\Modules\Api\Login;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;

readonly class ApiLoginResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string token = 'token';

    // Not PersonalAccessTokens::token->comment(): that column stores the hash,
    // while the response carries the plain text token, which is never stored.
    #[Response([Response::description => 'API authentication token'])]
    public string $token;
}
