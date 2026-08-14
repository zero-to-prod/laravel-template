<?php

use App\Models\User;
use App\Modules\PasswordConfirmation\PasswordConfirmationForm;
use App\Routes\Auth;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Support\Facades\Hash;

test('guests cannot access or submit password confirmation', function (): void {
    $this->get(Auth::confirmPassword->value)
        ->assertRedirect(Web::login->value);

    $this->post(Auth::confirmPassword->value, [
        PasswordConfirmationForm::password => 'password',
    ])->assertRedirect(Web::login->value);
});

test('the password confirmation page renders', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(Auth::confirmPassword->value)
        ->assertOk()
        ->assertSee('Confirm your password')
        ->assertSee('name="'.PasswordConfirmationForm::password.'"', false)
        ->assertSee('action="'.Auth::confirmPassword->value.'"', false);

    expect(route('password.confirm'))->toContain(Auth::confirmPassword->value);
});

test('a user can confirm their password and return to the intended page', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->withSession(['url.intended' => Auth::settingsCredentials->value])
        ->post(Auth::confirmPassword->value, [
            PasswordConfirmationForm::password => 'current-password',
        ])->assertRedirect(Auth::settingsCredentials->value)
        ->assertSessionHas('auth.password_confirmed_at');
});

test('password confirmation falls back to home without an intended page', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->post(Auth::confirmPassword->value, [
            PasswordConfirmationForm::password => 'current-password',
        ])->assertRedirect(Web::home->value)
        ->assertSessionHas('auth.password_confirmed_at');
});

test('an incorrect password is rejected', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Auth::confirmPassword->value)
        ->post(Auth::confirmPassword->value, [
            PasswordConfirmationForm::password => 'incorrect-password',
        ])->assertRedirect(Auth::confirmPassword->value)
        ->assertSessionHasErrors(PasswordConfirmationForm::password)
        ->assertSessionMissing('auth.password_confirmed_at');
});

test('a missing password is rejected', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::confirmPassword->value)
        ->post(Auth::confirmPassword->value)
        ->assertRedirect(Auth::confirmPassword->value)
        ->assertSessionHasErrors(PasswordConfirmationForm::password)
        ->assertSessionMissing('auth.password_confirmed_at');
});
