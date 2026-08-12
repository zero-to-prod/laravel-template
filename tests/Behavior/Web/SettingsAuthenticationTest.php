<?php

use App\Models\User;
use App\Modules\Settings\Authentication\PasswordForm;
use App\Routes\Web;
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
    $this->get(Web::settingsAuthentication->value)
        ->assertRedirect(Web::login->value);
});

test('guests cannot update a password', function (): void {
    $this->post(Web::settingsAuthentication->value, passwordForm())
        ->assertRedirect(Web::login->value);
});

test('the page renders the password form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::settingsAuthentication->value)
        ->assertOk()
        ->assertSee('Current Password')
        ->assertSee('New Password')
        ->assertSee(Web::settingsProfile->value);
});

test('a password is updated', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Web::settingsAuthentication->value)
        ->post(Web::settingsAuthentication->value, passwordForm('current-password'))
        ->assertRedirect(Web::settingsAuthentication->value)
        ->assertSessionHas('status', 'Password updated.');

    expect(Hash::check('new-password-1234', $User->refresh()->password))->toBeTrue();
});

test('validation fails with an incorrect current password', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Web::settingsAuthentication->value)
        ->post(Web::settingsAuthentication->value, passwordForm('wrong-password'))
        ->assertSessionHasErrors(PasswordForm::current_password);

    expect(Hash::check('current-password', $User->refresh()->password))->toBeTrue();
});

test('validation fails with a mismatched confirmation', function (): void {
    $User = User::factory()->createOne([
        Users::password->value => Hash::make('current-password'),
    ]);

    $this->actingAs($User)
        ->from(Web::settingsAuthentication->value)
        ->post(Web::settingsAuthentication->value, [
            ...passwordForm('current-password'),
            PasswordForm::password_confirmation => 'mismatch',
        ])
        ->assertSessionHasErrors(PasswordForm::password);

    expect(Hash::check('current-password', $User->refresh()->password))->toBeTrue();
});

test('validation fails with missing required fields', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Web::settingsAuthentication->value)
        ->post(Web::settingsAuthentication->value)
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
        ->from(Web::settingsAuthentication->value)
        ->followingRedirects()
        ->post(Web::settingsAuthentication->value, passwordForm('wrong-password'))
        ->assertOk()
        ->assertSee('The password is incorrect.');
});

test('the new password is never flashed back to the form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Web::settingsAuthentication->value)
        ->post(Web::settingsAuthentication->value, passwordForm('wrong-password'))
        ->assertSessionMissing(PasswordForm::password);
});
