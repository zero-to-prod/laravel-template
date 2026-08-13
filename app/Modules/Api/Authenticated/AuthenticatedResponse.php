<?php

namespace App\Modules\Api\Authenticated;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

readonly class AuthenticatedResponse
{
    use DataModel;
    use HasResponseSchema;
}
