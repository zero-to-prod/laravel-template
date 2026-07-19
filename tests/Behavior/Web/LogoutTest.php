<?php

namespace Tests\Behavior\Web;

use App\Models\User;
use App\Routes\Web;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    #[Test]
    public function route_is_accessible(): void
    {
        $this->get(Web::logout->value)->assertRedirect(Web::home->value);
    }

    #[Test]
    public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(Web::logout->value)
            ->assertRedirect(Web::home->value);

        $this->assertGuest();
    }

    #[Test]
    public function session_is_invalidated_after_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sessionId = session()->getId();

        $this->get(Web::logout->value);

        $this->assertGuest();
        $this->assertNotEquals($sessionId, session()->getId());
    }

    #[Test]
    public function guest_user_is_redirected_to_home(): void
    {
        $this->get(Web::logout->value)
            ->assertRedirect(Web::home->value);

        $this->assertGuest();
    }

    #[Test]
    public function session_token_is_regenerated_after_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $oldToken = session()->token();

        $this->get(Web::logout->value);

        $this->assertNotEquals($oldToken, session()->token());
    }
}
