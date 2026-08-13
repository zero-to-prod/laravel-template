<?php

namespace App\Modules\Api\CacheLocks\Destroy;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

readonly class CacheLocksDestroyResponse
{
    use DataModel;
    use HasResponseSchema;
}
