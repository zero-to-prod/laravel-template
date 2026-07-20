<?php

namespace Tests\Behavior\Web;

use App\DataModels\User;
use App\Modules\Register\RegisterConfig;
use App\Routes\Web;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\Factories\UserFactory;
use Tests\TestCase;

class RegisterRateLimitTest extends TestCase
{
    #[Test]
    public function registration_is_blocked_after_too_many_attempts(): void
    {
        $RegisterForm = UserFactory::factory()->make();
        $RegisterConfig = new RegisterConfig;

        $key = $RegisterConfig->rateLimitKey($RegisterForm->email);
        for ($i = 0; $i < $RegisterConfig->rateLimitMaxAttempts(); $i++) {
            RateLimiter::hit($key);
        }

        $this->post(Web::register->value, $RegisterForm->toArray())
            ->assertSessionHasErrors(User::email);

        $this->assertGuest();
        $this->assertDatabaseMissing((new \App\Models\User)->getTable(), [
            User::email => $RegisterForm->email,
        ]);
    }
}
