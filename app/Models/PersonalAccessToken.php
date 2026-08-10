<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Sanctum's token model, given the property docblock its own lacks.
 *
 * Nothing here changes behaviour. The vendor model declares no properties, so
 * every `$Token->expires_at` is an undefined-property error at phpstan level 9
 * and every endpoint that reads a token has to work around it. Sanctum's
 * documented extension point is a subclass registered with
 * `usePersonalAccessTokenModel`, so that is what this is.
 *
 * @property int $id
 * @property string $tokenable_type
 * @property string $tokenable_id
 * @property string $name
 * @property string $token
 * @property list<string>|null $abilities
 * @property Carbon|null $last_used_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PersonalAccessToken extends SanctumPersonalAccessToken {}
