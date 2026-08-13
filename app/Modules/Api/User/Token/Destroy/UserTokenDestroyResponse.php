<?php

namespace App\Modules\Api\User\Token\Destroy;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

readonly class UserTokenDestroyResponse
{
    use DataModel;
    use HasResponseSchema;
}
