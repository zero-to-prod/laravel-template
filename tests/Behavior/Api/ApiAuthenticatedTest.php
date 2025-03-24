<?php

namespace Tests\Behavior\Api;

use App\Models\User;
use App\Modules\Api\ApiResponse;
use App\Modules\Api\ResponseType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_access_endpoint(): void
    {
        $User = User::factory()->create();
        Sanctum::actingAs($User);

        $response = $this->getJson(api()->authenticated);

        $response->assertOk()
            ->assertJson([
                ApiResponse::success => true,
                ApiResponse::message => ResponseType::authorized->value,
                ApiResponse::type => ResponseType::authorized->value
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_endpoint(): void
    {
        $response = $this->getJson(api()->authenticated);

        $response->assertStatus(401)
            ->assertJson([
                ApiResponse::success => false,
                ApiResponse::message => 'unauthorized',
                ApiResponse::type => ResponseType::error->value
            ]);
    }

    #[Test]
    public function expired_token_cannot_access_endpoint(): void
    {
        $User = User::factory()->create();
        $token = $User->createToken('test-token');
        $token->accessToken->expires_at = now()->subDay();
        $token->accessToken->save();

        $this->withToken($token->plainTextToken)
            ->getJson(api()->authenticated)
            ->assertStatus(401);
    }

    #[Test]
    public function invalid_token_cannot_access_endpoint(): void
    {
        $this->withToken('invalid-token')
            ->getJson(api()->authenticated)
            ->assertStatus(401);
    }

    #[Test]
    public function multiple_tokens_work_independently(): void
    {
        $User = User::factory()->create();

        $token1 = $User->createToken('device-1')->plainTextToken;
        $token2 = $User->createToken('device-2')->plainTextToken;

        $this->withToken($token1)
            ->getJson(api()->authenticated)
            ->assertOk();

        $this->withToken($token2)
            ->getJson(api()->authenticated)
            ->assertOk();
    }

    #[Test]
    public function response_structure_is_correct(): void
    {
        $User = User::factory()->create();
        Sanctum::actingAs($User);

        $this->getJson(api()->authenticated)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'type',
                'data',
                'errors'
            ]);
    }
}