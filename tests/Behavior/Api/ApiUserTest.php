<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\User\ApiUserResponse;
use App\Routes\ApiRoute;

test('authenticated user can retrieve their details', function (): void {
    $User = User::factory()->createOne();

    $response = $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)->getJson(ApiRoute::user->value)
    );

    $response->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(ApiUserResponse::class),
            ApiResponse::data => [
                ApiUserResponse::id => $User->id,
                ApiUserResponse::name => $User->name,
                ApiUserResponse::email => $User->email,
                // The model serializes its dates, so the response carries
                // whatever Model::serializeDate() published.
                ApiUserResponse::email_verified_at => $User->toArray()[ApiUserResponse::email_verified_at],
                ApiUserResponse::created_at => $User->toArray()[ApiUserResponse::created_at],
                ApiUserResponse::updated_at => $User->toArray()[ApiUserResponse::updated_at],
            ],
        ]);
});

test('the password is never exposed', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::user->value)
        ->assertOk()
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token');
});

test('an unverified email is published as null', function (): void {
    $User = User::factory()->unverified()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)->getJson(ApiRoute::user->value)
    )->assertOk()->assertJsonPath('data.email_verified_at', null);
});

test('unauthenticated user cannot retrieve user details', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::user->value)
    )->assertStatus(401);
});
