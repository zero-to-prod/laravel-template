<?php

namespace App\Modules\Api\Models;

use App\Helpers\DataModel;
use App\Modules\Api\Support\Field;

readonly class ApiToken
{
    use DataModel;

    /** @link $token */
    public const string token = 'token';
    #[Field('API authentication token')]
    public string $token;
}