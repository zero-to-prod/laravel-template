<?php

namespace Tests\Behavior\Api;

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiLogoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_logout(): void
    {
        $User = User::factory()->create();
        $token = $User->createToken('test-device')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(api()->logout);

        $response->assertOk()
            ->assertJson([
                ApiResponse::success => true,
                ApiResponse::message => 'Logout',
                ApiResponse::type => 'Logout'
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $User->id,
            'name' => 'test-device'
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson(api()->logout);

        $response->assertStatus(401);
    }

    #[Test]
    public function logout_only_removes_current_token(): void
    {
        $User = User::factory()->create();
        $token1 = $User->createToken('device-1')->plainTextToken;
        $token2 = $User->createToken('device-2')->plainTextToken;

        $this->withToken($token1)
            ->postJson(api()->logout)
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $User->id,
            'name' => 'device-1'
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $User->id,
            'name' => 'device-2'
        ]);

        // Second token should still work
        $this->withToken($token2)
            ->getJson(api()->authenticated)
            ->assertOk();
    }

    #[Test]
    public function expired_token_cannot_logout(): void
    {
        $User = User::factory()->create();
        $token = $User->createToken('test-token');
        $token->accessToken->expires_at = now()->subDay();
        $token->accessToken->save();

        $this->withToken($token->plainTextToken)
            ->postJson(api()->logout)
            ->assertStatus(401);
    }

    #[Test]
    public function invalid_token_cannot_logout(): void
    {
        $this->withToken('invalid-token')
            ->postJson(api()->logout)
            ->assertStatus(401);
    }

    #[Test]
    public function response_structure_is_correct(): void
    {
        $User = User::factory()->create();
        Sanctum::actingAs($User);

        $this->postJson(api()->logout)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'type',
            ]);
    }

    #[Test]
    public function logged_out_token_cannot_be_reused(): void
    {
        $User = User::factory()->create();
        $token = $User->createToken('test-device')->plainTextToken;

        $this->withToken($token)
            ->postJson(api()->logout)
            ->assertOk();

        $this->withToken($token)
            ->postJson(api()->authenticated)
            ->assertStatus(405);
    }
}