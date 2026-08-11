<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
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
        ->assertJsonPath('data.tokens.1.'.UserTokenShowResponse::abilities, ['tokens:read'])
        ->assertJsonPath('data.'.UserTokenIndexResponse::pagination, [
            PaginationResponse::page => 1,
            PaginationResponse::per_page => PaginationParameters::default_per_page,
            PaginationResponse::total => 2,
            PaginationResponse::last_page => 1,
        ]);
});

test('the list is paged', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('first-device')->plainTextToken;
    $User->createToken('second-device');
    $User->createToken('third-device');

    $this->assertMatchesSchema(
        $this->withToken($token)->getJson(ApiRoute::user_tokens->value.'?per_page=2')
    )->assertOk()
        ->assertJsonCount(2, 'data.'.UserTokenIndexResponse::tokens)
        ->assertJsonPath('data.tokens.0.'.UserTokenShowResponse::name, 'first-device')
        ->assertJsonPath('data.'.UserTokenIndexResponse::pagination, [
            PaginationResponse::page => 1,
            PaginationResponse::per_page => 2,
            PaginationResponse::total => 3,
            PaginationResponse::last_page => 2,
        ]);

    $this->withToken($token)
        ->getJson(ApiRoute::user_tokens->value.'?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data.'.UserTokenIndexResponse::tokens)
        ->assertJsonPath('data.tokens.0.'.UserTokenShowResponse::name, 'third-device')
        ->assertJsonPath('data.pagination.'.PaginationResponse::page, 2);
});

test('a per_page above the maximum is served as the maximum', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::user_tokens->value.'?per_page=1000')
        ->assertOk()
        ->assertJsonPath('data.pagination.'.PaginationResponse::per_page, PaginationParameters::max_per_page);
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
