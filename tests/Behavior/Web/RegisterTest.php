<?php

use App\DataModels\User;
use App\Models\User as ModelUser;
use App\Routes\Web;
use Illuminate\Support\Facades\Hash;
use Tests\Factories\UserFactory;

test('route is accessible', function (): void {
    $this->get(Web::register->value)->assertOk();
});

test('registration', function (): void {
    $RegisterForm = UserFactory::factory()->make();

    $this->post(
        Web::register->value,
        $RegisterForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
    $this->assertDatabaseHas((new ModelUser)->getTable(), [
        User::name => $RegisterForm->name,
        User::email => $RegisterForm->email,
    ]);
});

test('validation fails with invalid name', function (): void {
    $this->post(
        Web::register->value,
        UserFactory::factory()->set([User::name => ''])->context()
    )->assertSessionHasErrors(User::name);

    $this->assertGuest();
});

test('validation fails with invalid email', function (): void {
    $this->post(
        Web::register->value,
        UserFactory::factory()->set([User::email => ''])->context()
    )->assertSessionHasErrors(User::email);

    $this->assertGuest();
});

test('validation fails with duplicate email', function (): void {
    $RegisterForm = UserFactory::factory()->make();
    ModelUser::factory()->createOne([User::email => $RegisterForm->email]);

    $this->post(
        Web::register->value,
        $RegisterForm->toArray()
    )->assertSessionHasErrors(User::email);

    $this->assertGuest();
});

test('validation fails with mismatched passwords', function (): void {
    $this->post(
        Web::register->value,
        UserFactory::factory()->set([User::password_confirmation => 'mismatch'])->context()
    )->assertSessionHasErrors(User::password);

    $this->assertGuest();
});

test('password is hashed after registration', function (): void {
    $RegisterForm = UserFactory::factory()->make();

    $this->post(Web::register->value, $RegisterForm->toArray());

    $user = ModelUser::query()->where(User::email, $RegisterForm->email)->firstOrFail();
    expect($user->password)->not->toBe($RegisterForm->password)
        ->and(Hash::check($RegisterForm->password, $user->password))->toBeTrue();
});

test('validation fails with missing required fields', function (): void {
    $this->post(Web::register->value)
        ->assertSessionHasErrors([
            User::name,
            User::email,
            User::password,
        ]);

    $this->assertGuest();
});

test('validation errors are displayed on the form', function (): void {
    $this->from(Web::register->value)
        ->followingRedirects()
        ->post(
            Web::register->value,
            UserFactory::factory()->set([User::name => ''])->context()
        )
        ->assertOk()
        ->assertSee('The name field is required.');
});

test('old input is preserved on validation failure', function (): void {
    $RegisterForm = UserFactory::factory()
        ->set([User::email => 'invalid-email'])
        ->make();

    $this->post(Web::register->value, $RegisterForm->toArray())
        ->assertSessionHasInput($RegisterForm->name)
        ->assertSessionMissing($RegisterForm->password);

    $this->assertGuest();
});

test('intended url is preserved after registration', function (): void {
    session(['url.intended' => Web::home->value]);

    $this->post(
        Web::register->value,
        UserFactory::factory()->make()->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});
