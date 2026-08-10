<?php

use App\Models\User;
use App\Modules\Register\RegisterFormFactory;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Support\Facades\RateLimiter;

test('registration is blocked after too many attempts', function (): void {
    $RegisterForm = RegisterFormFactory::factory()->make();

    $key = 'register:'.$RegisterForm->email;
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit($key);
    }

    $this->post(Web::register->value, $RegisterForm->toArray())
        ->assertSessionHasErrors(Users::email->value);

    $this->assertGuest();
    $this->assertDatabaseMissing((new User)->getTable(), [
        Users::email->value => $RegisterForm->email,
    ]);
});
