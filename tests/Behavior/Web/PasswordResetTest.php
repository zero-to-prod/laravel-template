<?php

use App\Models\User;
use App\Modules\PasswordReset\ForgotPasswordForm;
use App\Modules\PasswordReset\ResetPasswordForm;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('the forgot password page is linked from login and renders', function (): void {
    $this->get(Web::login->value)
        ->assertOk()
        ->assertSee(Web::forgotPassword->value);

    $this->get(Web::forgotPassword->value)
        ->assertOk()
        ->assertSee('Reset your password')
        ->assertSee('Email reset link');
});

test('authenticated users are redirected away from password reset pages', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(Web::forgotPassword->value)
        ->assertRedirect(Web::home->value);

    $this->actingAs($User)
        ->get(Web::resetPassword->url([ResetPasswordForm::token => 'token']))
        ->assertRedirect(Web::home->value);

    $this->actingAs($User)
        ->get(Web::forgotPasswordSent->value)
        ->assertRedirect(Web::home->value);
});

test('the password reset confirmation page renders next steps', function (): void {
    $this->get(Web::forgotPasswordSent->value)
        ->assertOk()
        ->assertSee('Check your email')
        ->assertSee('If an account exists for that email')
        ->assertSee(Web::login->value)
        ->assertSee(Web::forgotPassword->value);
});

test('a password reset link is sent to an existing user', function (): void {
    Notification::fake();
    $User = User::factory()->createOne();

    $this->post(Web::forgotPassword->value, [
        ForgotPasswordForm::email => strtoupper($User->email),
    ])->assertRedirect(Web::forgotPasswordSent->value);

    Notification::assertSentTo($User, ResetPassword::class);
});

test('forgot password does not disclose whether an account exists', function (): void {
    Notification::fake();

    $this->post(Web::forgotPassword->value, [
        ForgotPasswordForm::email => 'missing@example.com',
    ])->assertRedirect(Web::forgotPasswordSent->value);

    Notification::assertNothingSent();
});

test('forgot password validates the email', function (): void {
    $this->from(Web::forgotPassword->value)
        ->post(Web::forgotPassword->value, [ForgotPasswordForm::email => 'not-an-email'])
        ->assertRedirect(Web::forgotPassword->value)
        ->assertSessionHasErrors(ForgotPasswordForm::email);
});

test('the reset password page renders the token action and email', function (): void {
    $url = Web::resetPassword->url([ResetPasswordForm::token => 'reset-token']);

    $this->get($url.'?email=user%40example.com')
        ->assertOk()
        ->assertSee('Choose a new password')
        ->assertSee('user@example.com')
        ->assertSee('action="'.$url.'"', false);

    expect(route('password.reset', [
        ResetPasswordForm::token => 'reset-token',
        ResetPasswordForm::email => 'user@example.com',
    ]))->toContain($url.'?email=user%40example.com');
});

test('a password can be reset with a valid token', function (): void {
    Event::fake([PasswordResetEvent::class]);
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('old-password'),
        Users::remember_token->value => 'old-remember-token',
    ]);
    $token = Password::createToken($User);
    $url = Web::resetPassword->url([ResetPasswordForm::token => $token]);

    $this->post($url, [
        ResetPasswordForm::email => $User->email,
        ResetPasswordForm::password => 'new-password-1234',
        ResetPasswordForm::password_confirmation => 'new-password-1234',
    ])->assertRedirect(Web::login->value)
        ->assertSessionHas('status', trans(Password::PasswordReset));

    $User->refresh();
    expect(Hash::check('new-password-1234', $User->password))->toBeTrue()
        ->and($User->remember_token)->not->toBe('old-remember-token');
    Event::assertDispatched(PasswordResetEvent::class);
});

test('an invalid password reset token is rejected', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('old-password'),
    ]);
    $url = Web::resetPassword->url([ResetPasswordForm::token => 'invalid-token']);

    $this->from($url)->post($url, [
        ResetPasswordForm::email => $User->email,
        ResetPasswordForm::password => 'new-password-1234',
        ResetPasswordForm::password_confirmation => 'new-password-1234',
    ])->assertRedirect($url)
        ->assertSessionHasErrors(ResetPasswordForm::email)
        ->assertSessionHasInput(ResetPasswordForm::email, $User->email);

    expect(Hash::check('old-password', $User->refresh()->password))->toBeTrue();
});

test('reset password validates the submitted fields', function (): void {
    $url = Web::resetPassword->url([ResetPasswordForm::token => 'token']);

    $this->from($url)->post($url, [
        ResetPasswordForm::email => 'invalid',
        ResetPasswordForm::password => 'short',
        ResetPasswordForm::password_confirmation => 'different',
    ])->assertRedirect($url)
        ->assertSessionHasErrors([
            ResetPasswordForm::email,
            ResetPasswordForm::password,
        ])
        ->assertSessionMissing(ResetPasswordForm::password);
});
