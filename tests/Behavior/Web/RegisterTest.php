<?php

use App\Models\User;
use App\Modules\Register\RegisterForm;
use App\Modules\Register\RegisterFormFactory;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Support\Facades\Hash;

test('route is accessible', function (): void {
    $this->get(Web::register->value)->assertOk();
});

test('registration', function (): void {
    $RegisterForm = RegisterFormFactory::factory()->make();

    $this->post(
        Web::register->value,
        $RegisterForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
    $this->assertDatabaseHas((new User)->getTable(), [
        Users::name->value => $RegisterForm->name,
        Users::email->value => $RegisterForm->email,
    ]);
});

test('validation fails with invalid name', function (): void {
    $this->post(
        Web::register->value,
        RegisterFormFactory::factory()->set([RegisterForm::name => ''])->context()
    )->assertSessionHasErrors(RegisterForm::name);

    $this->assertGuest();
});

test('validation fails with invalid email', function (): void {
    $this->post(
        Web::register->value,
        RegisterFormFactory::factory()->set([RegisterForm::email => ''])->context()
    )->assertSessionHasErrors(RegisterForm::email);

    $this->assertGuest();
});

test('validation fails with duplicate email', function (): void {
    $RegisterForm = RegisterFormFactory::factory()->make();
    User::factory()->createOne([Users::email->value => $RegisterForm->email]);

    $this->post(
        Web::register->value,
        $RegisterForm->toArray()
    )->assertSessionHasErrors(RegisterForm::email);

    $this->assertGuest();
});

test('validation fails with mismatched passwords', function (): void {
    $this->post(
        Web::register->value,
        RegisterFormFactory::factory()->set([RegisterForm::password_confirmation => 'mismatch'])->context()
    )->assertSessionHasErrors(RegisterForm::password);

    $this->assertGuest();
});

test('password is hashed after registration', function (): void {
    $RegisterForm = RegisterFormFactory::factory()->make();

    $this->post(Web::register->value, $RegisterForm->toArray());

    $User = User::query()->where(Users::email->value, $RegisterForm->email)->firstOrFail();
    expect($User->password)->not->toBe($RegisterForm->password)
        ->and(Hash::check($RegisterForm->password, $User->password))->toBeTrue();
});

test('validation fails with missing required fields', function (): void {
    $this->post(Web::register->value)
        ->assertSessionHasErrors([
            RegisterForm::name,
            RegisterForm::email,
            RegisterForm::password,
        ]);

    $this->assertGuest();
});

test('validation errors are displayed on the form', function (): void {
    $this->from(Web::register->value)
        ->followingRedirects()
        ->post(
            Web::register->value,
            RegisterFormFactory::factory()->set([RegisterForm::name => ''])->context()
        )
        ->assertOk()
        ->assertSee('The name field is required.');
});

test('old input is preserved on validation failure', function (): void {
    $RegisterForm = RegisterFormFactory::factory()
        ->set([RegisterForm::email => 'invalid-email'])
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
        RegisterFormFactory::factory()->make()->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});
