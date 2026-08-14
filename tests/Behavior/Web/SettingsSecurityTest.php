<?php

use App\Helpers\OauthProviderId;
use App\Models\User;
use App\Modules\Settings\Authentication\PasswordForm;
use App\Routes\Auth;
use App\Routes\Web;
use App\Sources\Db\App\OauthProviders;
use App\Sources\Db\App\Users;
use Illuminate\Support\Facades\Hash;

/** @return array<string, string> */
function passwordForm(string $current = 'password', string $new = 'new-password-1234'): array
{
    return [
        PasswordForm::current_password => $current,
        PasswordForm::password => $new,
        PasswordForm::password_confirmation => $new,
    ];
}

test('guests are redirected to login', function (): void {
    $this->get(Auth::settingsSecurity->value)
        ->assertRedirect(Web::login->value);
});

test('guests cannot update a password', function (): void {
    $this->post(Auth::settingsSecurity->value, passwordForm())
        ->assertRedirect(Web::login->value);
});

test('the page renders the password form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settingsSecurity->value)
        ->assertOk()
        ->assertSee('Current Password')
        ->assertSee('New Password')
        ->assertSee('Sign in methods')
        ->assertSee('No connected providers.')
        ->assertSee(Auth::settingsProfile->value);
});

test('the page lists the users sign in providers', function (): void {
    $User = User::factory()->createOne();
    $User->oauthProviders()->create([
        OauthProviders::provider_id->value => OauthProviderId::google->value,
        OauthProviders::sub->value => '123456789',
        OauthProviders::name->value => 'Google User',
        OauthProviders::given_name->value => 'Google',
        OauthProviders::family_name->value => 'User',
        OauthProviders::picture->value => 'https://example.com/avatar.jpg',
        OauthProviders::email->value => 'google@example.com',
        OauthProviders::email_verified->value => true,
        OauthProviders::hd->value => 'example.com',
        OauthProviders::id->value => '123456789',
        OauthProviders::verified_email->value => true,
    ]);

    $this->actingAs($User)
        ->get(Auth::settingsSecurity->value)
        ->assertOk()
        ->assertSee('Google')
        ->assertSee('Google User')
        ->assertSee('google@example.com')
        ->assertSee('example.com')
        ->assertSee('https://example.com/avatar.jpg')
        ->assertSee('Verified');
});

test('a password is updated', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Auth::settingsSecurity->value)
        ->post(Auth::settingsSecurity->value, passwordForm('current-password'))
        ->assertRedirect(Auth::settingsSecurity->value)
        ->assertSessionHas('status', 'Password updated.');

    expect(Hash::check('new-password-1234', $User->refresh()->password))->toBeTrue();
});

test('validation fails with an incorrect current password', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Auth::settingsSecurity->value)
        ->post(Auth::settingsSecurity->value, passwordForm('wrong-password'))
        ->assertSessionHasErrors(PasswordForm::current_password);

    expect(Hash::check('current-password', $User->refresh()->password))->toBeTrue();
});

test('validation fails with a mismatched confirmation', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Auth::settingsSecurity->value)
        ->post(Auth::settingsSecurity->value, [
            ...passwordForm('current-password'),
            PasswordForm::password_confirmation => 'mismatch',
        ])
        ->assertSessionHasErrors(PasswordForm::password);

    expect(Hash::check('current-password', $User->refresh()->password))->toBeTrue();
});

test('validation fails with missing required fields', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsSecurity->value)
        ->post(Auth::settingsSecurity->value)
        ->assertSessionHasErrors([
            PasswordForm::current_password,
            PasswordForm::password,
        ]);
});

test('validation errors are displayed on the form', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Auth::settingsSecurity->value)
        ->followingRedirects()
        ->post(Auth::settingsSecurity->value, passwordForm('wrong-password'))
        ->assertOk()
        ->assertSee('The password is incorrect.');
});

test('the new password is never flashed back to the form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsSecurity->value)
        ->post(Auth::settingsSecurity->value, passwordForm('wrong-password'))
        ->assertSessionMissing(PasswordForm::password);
});
