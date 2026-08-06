<?php

namespace App\Modules\Api\Models;

use App\Helpers\DataModel;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class ApiToken
{
    use DataModel;

    /** @link $token */
    public const string token = 'token';

    #[Describe([
        Field::field => [Field::description => 'API authentication token'],
    ])]
    public string $token;
}
