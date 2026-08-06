<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\ApiRoute;
use Laravel\Sanctum\Sanctum;

test('authenticated user can access endpoint', function (): void {
    $User = User::factory()->create();
    Sanctum::actingAs($User);

    $response = $this->assertMatchesSchema(
        $this->withToken('any-value')->getJson(ApiRoute::authenticated->value)
    );

    $response->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::message => 'Authorized',
            ApiResponse::type => 'Authorized',
        ]);
});

test('unauthenticated user cannot access endpoint', function (): void {
    $response = $this->getJson(ApiRoute::authenticated->value);

    $response->assertStatus(401)
        ->assertJson([
            ApiResponse::success => false,
            ApiResponse::message => 'unauthorized',
            ApiResponse::type => 'error',
        ]);
});

test('expired token cannot access endpoint', function (): void {
    $User = User::factory()->create();
    $token = $User->createToken('test-token');
    $token->accessToken->expires_at = now()->subDay();
    $token->accessToken->save();

    $this->withToken($token->plainTextToken)
        ->getJson(ApiRoute::authenticated->value)
        ->assertStatus(401);
});

test('invalid token cannot access endpoint', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::authenticated->value)
    )->assertStatus(401);
});

test('multiple tokens work independently', function (): void {
    $User = User::factory()->create();

    $token1 = $User->createToken('device-1')->plainTextToken;
    $token2 = $User->createToken('device-2')->plainTextToken;

    $this->withToken($token1)
        ->getJson(ApiRoute::authenticated->value)
        ->assertOk();

    $this->withToken($token2)
        ->getJson(ApiRoute::authenticated->value)
        ->assertOk();
});

test('response structure is correct', function (): void {
    $User = User::factory()->create();
    Sanctum::actingAs($User);

    $this->getJson(ApiRoute::authenticated->value)
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'type',
        ]);
});
