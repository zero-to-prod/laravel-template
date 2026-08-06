<?php

use App\DataModels\User;
use App\Models\User as ModelUser;
use App\Modules\Register\RegisterConfig;
use App\Routes\Web;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Factories\UserFactory;

test('registration is blocked after too many attempts', function (): void {
    $RegisterForm = UserFactory::factory()->make();
    $RegisterConfig = new RegisterConfig;

    $key = $RegisterConfig->rateLimitKey($RegisterForm->email);
    for ($i = 0; $i < $RegisterConfig->rateLimitMaxAttempts(); $i++) {
        RateLimiter::hit($key);
    }

    $this->post(Web::register->value, $RegisterForm->toArray())
        ->assertSessionHasErrors(User::email);

    $this->assertGuest();
    $this->assertDatabaseMissing((new ModelUser)->getTable(), [
        User::email => $RegisterForm->email,
    ]);
});
