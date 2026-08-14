<?php

use App\Models\User;
use App\Modules\Settings\Profile\ProfileForm;
use App\Routes\Auth;
use App\Routes\Web;
use App\Sources\Db\App\Users;

test('guests are redirected to login', function (): void {
    $this->get(Auth::settingsProfile->value)
        ->assertRedirect(Web::login->value);
});

test('guests cannot update a name', function (): void {
    $this->post(Auth::settingsProfile->value, [ProfileForm::name => 'Jane Doe'])
        ->assertRedirect(Web::login->value);
});

test('the settings root redirects to the profile section', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settings->value)
        ->assertRedirect(Auth::settingsProfile->value);
});

test('the page renders the nav and the immutable profile fields', function (): void {
    $User = User::factory()->createOne([
        Users::name->value => 'John Doe',
        Users::email->value => 'john@example.com',
    ]);

    $this->actingAs($User)
        ->get(Auth::settingsProfile->value)
        ->assertOk()
        ->assertSee('Profile')
        ->assertSee('Security')
        ->assertSee(Auth::settingsSecurity->value)
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('Verified')
        ->assertSee('name="email"', false)
        ->assertSee('readonly', false);
});

test('an unverified email is not marked as verified', function (): void {
    $this->actingAs(User::factory()->unverified()->createOne())
        ->get(Auth::settingsProfile->value)
        ->assertOk()
        ->assertDontSee('Verified');
});

test('a name is updated', function (): void {
    $User = User::factory()->createOne([Users::name->value => 'John Doe']);

    $this->actingAs($User)
        ->from(Auth::settingsProfile->value)
        ->post(Auth::settingsProfile->value, [ProfileForm::name => 'Jane Doe'])
        ->assertRedirect(Auth::settingsProfile->value)
        ->assertSessionHas('status', 'Profile updated.');

    expect($User->refresh()->name)->toBe('Jane Doe');
});

test('the email cannot be updated from the profile form', function (): void {
    $User = User::factory()->createOne([
        Users::email->value => 'john@example.com',
    ]);

    $this->actingAs($User)
        ->from(Auth::settingsProfile->value)
        ->post(Auth::settingsProfile->value, [
            ProfileForm::name => 'John Doe',
            Users::email->value => 'jane@example.com',
        ])
        ->assertRedirect(Auth::settingsProfile->value);

    expect($User->refresh()->email)->toBe('john@example.com');
});

test('a name is squished before it is stored', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::settingsProfile->value)
        ->post(Auth::settingsProfile->value, [ProfileForm::name => '  Jane   Doe  ']);

    expect($User->refresh()->name)->toBe('Jane Doe');
});

test('validation fails with a missing name', function (): void {
    $User = User::factory()->createOne([Users::name->value => 'John Doe']);

    $this->actingAs($User)
        ->from(Auth::settingsProfile->value)
        ->post(Auth::settingsProfile->value)
        ->assertRedirect(Auth::settingsProfile->value)
        ->assertSessionHasErrors(ProfileForm::name);

    expect($User->refresh()->name)->toBe('John Doe');
});

test('validation errors are displayed on the form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsProfile->value)
        ->followingRedirects()
        ->post(Auth::settingsProfile->value, [ProfileForm::name => ''])
        ->assertOk()
        ->assertSee('The name field is required.');
});

test('old input is preserved on validation failure', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsProfile->value)
        ->post(Auth::settingsProfile->value, [ProfileForm::name => str_repeat('a', 256)])
        ->assertSessionHasErrors(ProfileForm::name)
        ->assertSessionHasInput(ProfileForm::name, str_repeat('a', 256));
});
