<?php

namespace Tests\Behavior;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test] public function route_is_accessible(): void
    {
        $this->get(r()->logout())->assertRedirect(r()->home());
    }

    #[Test] public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(r()->logout())
            ->assertRedirect(r()->home());

        $this->assertGuest();
    }

    #[Test] public function session_is_invalidated_after_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sessionId = session()->getId();

        $this->get(web()->logout);

        $this->assertGuest();
        $this->assertNotEquals($sessionId, session()->getId());
    }

    #[Test] public function guest_user_is_redirected_to_home(): void
    {
        $this->get(r()->logout())
            ->assertRedirect(r()->home());

        $this->assertGuest();
    }

    #[Test] public function session_token_is_regenerated_after_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $oldToken = session()->token();

        $this->get(r()->logout());

        $this->assertNotEquals($oldToken, session()->token());
    }
}