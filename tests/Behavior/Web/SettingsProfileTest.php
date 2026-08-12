<?php

use App\Models\User;
use App\Modules\Settings\Profile\ProfileForm;
use App\Routes\Web;
use App\Sources\Db\App\Users;

test('guests are redirected to login', function (): void {
    $this->get(Web::settingsProfile->value)
        ->assertRedirect(Web::login->value);
});

test('guests cannot update a name', function (): void {
    $this->post(Web::settingsProfile->value, [ProfileForm::name => 'Jane Doe'])
        ->assertRedirect(Web::login->value);
});

test('the settings root redirects to the profile section', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::settings->value)
        ->assertRedirect(Web::settingsProfile->value);
});

test('the page renders the nav and the current name', function (): void {
    $User = User::factory()->createOne([Users::name->value => 'John Doe']);

    $this->actingAs($User)
        ->get(Web::settingsProfile->value)
        ->assertOk()
        ->assertSee('Profile')
        ->assertSee('Authentication')
        ->assertSee(Web::settingsAuthentication->value)
        ->assertSee('John Doe');
});

test('a name is updated', function (): void {
    $User = User::factory()->createOne([Users::name->value => 'John Doe']);

    $this->actingAs($User)
        ->from(Web::settingsProfile->value)
        ->post(Web::settingsProfile->value, [ProfileForm::name => 'Jane Doe'])
        ->assertRedirect(Web::settingsProfile->value)
        ->assertSessionHas('status', 'Profile updated.');

    expect($User->refresh()->name)->toBe('Jane Doe');
});

test('a name is squished before it is stored', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Web::settingsProfile->value)
        ->post(Web::settingsProfile->value, [ProfileForm::name => '  Jane   Doe  ']);

    expect($User->refresh()->name)->toBe('Jane Doe');
});

test('validation fails with a missing name', function (): void {
    $User = User::factory()->createOne([Users::name->value => 'John Doe']);

    $this->actingAs($User)
        ->from(Web::settingsProfile->value)
        ->post(Web::settingsProfile->value)
        ->assertRedirect(Web::settingsProfile->value)
        ->assertSessionHasErrors(ProfileForm::name);

    expect($User->refresh()->name)->toBe('John Doe');
});

test('validation errors are displayed on the form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Web::settingsProfile->value)
        ->followingRedirects()
        ->post(Web::settingsProfile->value, [ProfileForm::name => ''])
        ->assertOk()
        ->assertSee('The name field is required.');
});

test('old input is preserved on validation failure', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Web::settingsProfile->value)
        ->post(Web::settingsProfile->value, [ProfileForm::name => str_repeat('a', 256)])
        ->assertSessionHasErrors(ProfileForm::name)
        ->assertSessionHasInput(ProfileForm::name, str_repeat('a', 256));
});
