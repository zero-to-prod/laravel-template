<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\User\Token\Store\UserTokenStoreRequest;
use App\Modules\Api\User\Token\Store\UserTokenStoreResponse;
use App\Routes\ApiRoute;
use App\Sources\Db\App\PersonalAccessTokens;

test('authenticated user can issue a token', function (): void {
    $User = User::factory()->createOne();

    $response = $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::user_tokens->value, [UserTokenStoreRequest::name => 'ci-runner'])
    );

    $response->assertStatus(201)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(UserTokenStoreResponse::class),
            ApiResponse::data => [
                UserTokenStoreResponse::name => 'ci-runner',
                // Omitted abilities grant Sanctum's wildcard.
                UserTokenStoreResponse::abilities => UserTokenStoreRequest::all_abilities,
                UserTokenStoreResponse::expires_at => null,
            ],
        ]);

    $this->assertDatabaseHas(PersonalAccessTokens::table(), [
        PersonalAccessTokens::id->value => $response->json('data.'.UserTokenStoreResponse::id),
        PersonalAccessTokens::tokenable_id->value => $User->id,
        PersonalAccessTokens::name->value => 'ci-runner',
    ]);
});

test('the issued plain text token authenticates and is never stored', function (): void {
    $User = User::factory()->createOne();

    $plainText = $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::user_tokens->value, [UserTokenStoreRequest::name => 'ci-runner'])
        ->assertStatus(201)
        ->json('data.'.UserTokenStoreResponse::token);

    if (! is_string($plainText)) {
        $this->fail('The response carried no plain text token.');
    }

    // Sanctum publishes `{id}|{secret}` and stores only a digest of the secret.
    expect($plainText)->toContain('|');

    $this->assertDatabaseMissing(PersonalAccessTokens::table(), [
        PersonalAccessTokens::token->value => $plainText,
    ]);

    $this->withToken($plainText)->getJson(ApiRoute::user->value)->assertOk();
});

test('requested abilities are granted verbatim', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::user_tokens->value, [
                UserTokenStoreRequest::name => 'read-only',
                UserTokenStoreRequest::abilities => ['tokens:read', 'user:read'],
            ])
    )->assertStatus(201)
        ->assertJsonPath('data.'.UserTokenStoreResponse::abilities, ['tokens:read', 'user:read']);
});

test('a requested expiry is recorded on the token', function (): void {
    $User = User::factory()->createOne();
    $expiresAt = now()->addWeek()->startOfSecond();

    $id = $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::user_tokens->value, [
                UserTokenStoreRequest::name => 'short-lived',
                UserTokenStoreRequest::expires_at => $expiresAt->toIso8601String(),
            ])
    )->assertStatus(201)
        ->json('data.'.UserTokenStoreResponse::id);

    expect($User->tokens()->whereKey($id)->sole()->expires_at?->equalTo($expiresAt))->toBeTrue();
});

test('a blank name is rejected', function (): void {
    $User = User::factory()->createOne();

    // Blank is a server policy, not a published constraint: the document admits
    // the empty string, so the request still conforms and the 422 is reachable.
    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::user_tokens->value, [UserTokenStoreRequest::name => ''])
    )->assertStatus(422)
        ->assertJsonValidationErrors(UserTokenStoreRequest::name);
});

test('an expiry in the past is rejected', function (): void {
    $User = User::factory()->createOne();

    // A well-formed instant, so the document admits it and the check that
    // refuses it has to be the server's.
    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::user_tokens->value, [
            UserTokenStoreRequest::name => 'already-dead',
            UserTokenStoreRequest::expires_at => now()->subDay()->toIso8601String(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(UserTokenStoreRequest::expires_at);

    expect($User->tokens()->where(PersonalAccessTokens::name->value, 'already-dead')->exists())->toBeFalse();
});

test('a missing name is rejected', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::user_tokens->value, [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(UserTokenStoreRequest::name);
});

test('a non string ability is rejected', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::user_tokens->value, [
            UserTokenStoreRequest::name => 'bad-abilities',
            UserTokenStoreRequest::abilities => [1],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(UserTokenStoreRequest::abilities.'.0');
});

test('unauthenticated user cannot issue a token', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')
            ->postJson(ApiRoute::user_tokens->value, [UserTokenStoreRequest::name => 'ci-runner'])
    )->assertStatus(401);
});
