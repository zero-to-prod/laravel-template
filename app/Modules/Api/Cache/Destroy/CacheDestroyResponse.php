<?php

namespace App\Modules\Api\Cache\Destroy;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

readonly class CacheDestroyResponse
{
    use DataModel;
    use HasResponseSchema;
}
