<?php

use App\Models\PersonalAccessToken;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Behavior', 'Feature');
pest()->tia()->locally();

/**
 * The token Sanctum just issued, typed as the model this app registered.
 *
 * `NewAccessToken::$accessToken` is declared as Sanctum's own class, so it
 * carries none of the property docblocks `App\Models\PersonalAccessToken`
 * adds. Reading the row back through the relation is what restores them.
 */
function issuedToken(User $User, NewAccessToken $NewAccessToken): PersonalAccessToken
{
    return $User->tokens()->whereKey($NewAccessToken->accessToken->getKey())->sole();
}
