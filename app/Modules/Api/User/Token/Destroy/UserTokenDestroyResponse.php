<?php

namespace App\Modules\Api\User\Token\Destroy;

use App\Helpers\DataModel;
use App\Modules\Api\Support\HasResponseSchema;

/**
 * No properties: revocation has nothing to say beyond that it happened, and a
 * response DTO with none publishes the envelope without a `data` key.
 */
readonly class UserTokenDestroyResponse
{
    use DataModel;
    use HasResponseSchema;
}
