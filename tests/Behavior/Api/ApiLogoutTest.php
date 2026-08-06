<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\ApiRoute;
use Laravel\Sanctum\Sanctum;

test('authenticated user can logout', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device')->plainTextToken;

    $response = $this->assertMatchesSchema(
        $this->withToken($token)->postJson(ApiRoute::logout->value)
    );

    $response->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::message => 'Logout',
            ApiResponse::type => 'Logout',
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $User->id,
        'name' => 'test-device',
    ]);
});

test('unauthenticated user cannot logout', function (): void {
    $response = $this->postJson(ApiRoute::logout->value);

    $response->assertStatus(401);
});

test('logout only removes current token', function (): void {
    $User = User::factory()->createOne();
    $token1 = $User->createToken('device-1')->plainTextToken;
    $token2 = $User->createToken('device-2')->plainTextToken;

    $this->withToken($token1)
        ->postJson(ApiRoute::logout->value)
        ->assertOk();

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $User->id,
        'name' => 'device-1',
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $User->id,
        'name' => 'device-2',
    ]);

    // Second token should still work
    $this->withToken($token2)
        ->getJson(ApiRoute::authenticated->value)
        ->assertOk();
});

test('expired token cannot logout', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-token');
    $token->accessToken->forceFill(['expires_at' => now()->subDay()])->save();

    $this->withToken($token->plainTextToken)
        ->postJson(ApiRoute::logout->value)
        ->assertStatus(401);
});

test('invalid token cannot logout', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->postJson(ApiRoute::logout->value)
    )->assertStatus(401);
});

test('response structure is correct', function (): void {
    $User = User::factory()->createOne();
    Sanctum::actingAs($User);

    $this->postJson(ApiRoute::logout->value)
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'type',
        ]);
});

test('logged out token cannot be reused', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device')->plainTextToken;

    $this->withToken($token)
        ->postJson(ApiRoute::logout->value)
        ->assertOk();

    $this->withToken($token)
        ->postJson(ApiRoute::authenticated->value)
        ->assertStatus(405);
});
