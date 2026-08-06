<?php

use App\DataModels\User;
use App\Models\User as ModelUser;
use App\Modules\Login\LoginForm;
use App\Modules\Login\LoginFormFactory;
use App\Routes\Web;

test('route is accessible', function (): void {
    $this->get(Web::login->value)->assertOk();
});

test('login with valid credentials', function (): void {
    $User = ModelUser::factory([User::password => User::password])->create();
    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, $User->email)
        ->set(LoginForm::password, User::password)
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});

test('validation fails with invalid email', function (): void {
    $this->post(
        Web::login->value,
        LoginFormFactory::factory()->set(LoginForm::email, '')->context()
    )->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('validation fails with invalid password', function (): void {
    $this->post(
        Web::login->value,
        LoginFormFactory::factory()->set(LoginForm::password, '')->context()
    )->assertSessionHasErrors(LoginForm::password);

    $this->assertGuest();
});

test('login fails with invalid credentials', function (): void {
    $user = ModelUser::factory()->create();
    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, $user->email)
        ->set(LoginForm::password, 'wrong-password')
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('login fails with non existent user', function (): void {
    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, 'nonexistent@example.com')
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('user can login with remember me', function (): void {
    $User = ModelUser::factory()->create();
    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, $User->email)
        ->set(LoginForm::remember_token, true)
        ->make();

    $response = $this->post(
        Web::login->value,
        $LoginForm->toArray()
    );

    $response->assertRedirect(Web::home->value);
    $this->assertAuthenticatedAs($User);
    expect($User->fresh()->remember_token)->not->toBeNull();
});

test('user stays logged in with remember me', function (): void {
    $User = ModelUser::factory()->create();
    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, $User->email)
        ->set(LoginForm::remember_token, true)
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    );

    $this->post(Web::logout->value);
    $this->withSession([]);

    $this->get(Web::home->value);

    $this->assertAuthenticatedAs($User);
});

test('old input is preserved on validation failure', function (): void {
    $LoginForm = LoginFormFactory::factory()->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )
        ->assertSessionHasInput(LoginForm::email)
        ->assertSessionMissing(LoginForm::password);

    $this->assertGuest();
});

test('intended url is preserved after login', function (): void {
    $user = ModelUser::factory()->create();
    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, $user->email)
        ->make();

    session(['url.intended' => Web::home->value]);

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});

test('input is sanitized during login', function (): void {
    ModelUser::factory()->create([
        User::email => 'test@example.com',
    ]);

    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, ' TEST@EXAMPLE.COM ')
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});

test('validation fails with missing required fields', function (): void {
    $this->post(Web::login->value)
        ->assertSessionHasErrors([
            LoginForm::email,
            LoginForm::password,
        ]);

    $this->assertGuest();
});

test('user cannot login when already authenticated', function (): void {
    $user = ModelUser::factory()->create();
    $this->actingAs($user);

    $LoginForm = LoginFormFactory::factory()
        ->set(LoginForm::email, $user->email)
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);
});
