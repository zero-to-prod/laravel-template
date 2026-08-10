<?php

namespace App\Modules\Api\Login;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;
use App\Modules\Api\Support\Response;
use App\Sources\Db\App\PersonalAccessTokens;

readonly class ApiLoginResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string token = 'token';

    #[Response([Response::description => static function() {
        return PersonalAccessTokens::token->comment();
    }])]
    public string $token;
}
