<?php

namespace App\Modules\Api\Login;

use App\Helpers\DataModel;

readonly class ApiTokenResponse
{
    use DataModel;

    /** @link $token */
    public const token = 'token';
    public string $token;
}