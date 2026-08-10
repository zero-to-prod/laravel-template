<?php

namespace App\Modules\Api\Logout;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

readonly class LogoutResponse
{
    use DataModel;
    use HasResponseSchema;
}
