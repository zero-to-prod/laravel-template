<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\User\Token\Destroy\UserTokenDestroyResponse;
use App\Modules\Api\User\Token\TokenParameter;
use App\Routes\ApiRoute;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Support\Facades\Auth;

/**
 * A second request in the same test reuses the guard the first one resolved,
 * so a token revoked mid-test still authenticates until the guards are
 * dropped. The application is right; the harness is remembering.
 */
function forgetResolvedGuards(): void
{
    Auth::forgetGuards();
}

test('authenticated user can revoke one of their tokens', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device')->plainTextToken;
    $Subject = issuedToken($User, $User->createToken('subject-device'));

    $this->assertMatchesSchema(
        $this->withToken($token)->deleteJson(ApiRoute::user_token->url([TokenParameter::name => $Subject->id]))
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::message => class_basename(UserTokenDestroyResponse::class),
            ApiResponse::type => class_basename(UserTokenDestroyResponse::class),
        ]);

    $this->assertDatabaseMissing(PersonalAccessTokens::table(), [
        PersonalAccessTokens::id->value => $Subject->id,
    ]);

    // The token that authorised the revocation is untouched.
    $this->withToken($token)->getJson(ApiRoute::user_tokens->value)->assertOk();
});

test('a revoked token stops authenticating', function (): void {
    $User = User::factory()->createOne();
    $NewAccessToken = $User->createToken('doomed');
    $Revoked = issuedToken($User, $NewAccessToken);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->deleteJson(ApiRoute::user_token->url([TokenParameter::name => $Revoked->id]))
        ->assertOk();

    forgetResolvedGuards();

    $this->withToken($NewAccessToken->plainTextToken)->getJson(ApiRoute::user->value)->assertStatus(401);
});

test('a user can revoke the token they are using', function (): void {
    $User = User::factory()->createOne();
    $NewAccessToken = $User->createToken('self-revoking');

    $this->withToken($NewAccessToken->plainTextToken)
        ->deleteJson(ApiRoute::user_token->url([
            TokenParameter::name => issuedToken($User, $NewAccessToken)->id,
        ]))
        ->assertOk();

    forgetResolvedGuards();

    $this->withToken($NewAccessToken->plainTextToken)->getJson(ApiRoute::user->value)->assertStatus(401);
});

test('a token belonging to somebody else cannot be revoked', function (): void {
    $User = User::factory()->createOne();
    $Other = User::factory()->createOne();
    $Theirs = issuedToken($Other, $Other->createToken('somebody-elses'));

    // 404 rather than 403: a 403 would confirm the id names a real token.
    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->deleteJson(ApiRoute::user_token->url([TokenParameter::name => $Theirs->id]))
    )->assertStatus(404)
        ->assertJsonPath(ApiResponse::message, ErrorCode::token_not_found->value);

    $this->assertDatabaseHas(PersonalAccessTokens::table(), [
        PersonalAccessTokens::id->value => $Theirs->id,
    ]);
});

test('unauthenticated user cannot revoke a token', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->deleteJson(ApiRoute::user_token->url([TokenParameter::name => 1]))
    )->assertStatus(401);
});
