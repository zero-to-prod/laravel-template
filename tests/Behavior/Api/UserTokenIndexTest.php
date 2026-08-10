<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\User\Token\Index\UserTokenIndexResponse;
use App\Modules\Api\User\Token\Show\UserTokenShowResponse;
use App\Routes\ApiRoute;

test('authenticated user can list their tokens', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('first-device')->plainTextToken;
    $User->createToken('second-device', ['tokens:read']);

    $this->assertMatchesSchema(
        $this->withToken($token)->getJson(ApiRoute::user_tokens->value)
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(UserTokenIndexResponse::class),
        ])
        ->assertJsonCount(2, 'data.'.UserTokenIndexResponse::tokens)
        // Oldest first, so the token the request authenticated with leads.
        ->assertJsonPath('data.tokens.0.'.UserTokenShowResponse::name, 'first-device')
        ->assertJsonPath('data.tokens.0.'.UserTokenShowResponse::abilities, ['*'])
        ->assertJsonPath('data.tokens.1.'.UserTokenShowResponse::name, 'second-device')
        ->assertJsonPath('data.tokens.1.'.UserTokenShowResponse::abilities, ['tokens:read']);
});

test('the list never carries the stored digest', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::user_tokens->value)
        ->assertOk()
        ->assertJsonMissingPath('data.tokens.0.token')
        ->assertJsonMissingPath('data.tokens.0.tokenable_id');
});

test('the list holds only the callers own tokens', function (): void {
    $User = User::factory()->createOne();
    User::factory()->createOne()->createToken('somebody-elses');

    $this->withToken($User->createToken('mine')->plainTextToken)
        ->getJson(ApiRoute::user_tokens->value)
        ->assertOk()
        ->assertJsonCount(1, 'data.'.UserTokenIndexResponse::tokens)
        ->assertJsonPath('data.tokens.0.'.UserTokenShowResponse::name, 'mine');
});

test('unauthenticated user cannot list tokens', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::user_tokens->value)
    )->assertStatus(401);
});
