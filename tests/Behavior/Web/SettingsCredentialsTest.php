<?php

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Modules\Settings\Credentials\TokenForm;
use App\Routes\Admin;
use App\Routes\ApiRoute;
use App\Routes\Auth;
use App\Routes\Web;
use App\Sources\Db\App\PersonalAccessTokens;
use App\View\DataModels\CredentialsTable;

test('guests are redirected to login', function (): void {
    $this->get(Auth::settingsCredentials->value)
        ->assertRedirect(Web::login->value);
});

test('guests cannot create a token', function (): void {
    $this->post(Auth::settingsCredentials->value, [TokenForm::name => 'Laptop CLI'])
        ->assertRedirect(Web::login->value);

    $this->assertDatabaseMissing(PersonalAccessTokens::table(), [
        PersonalAccessTokens::name->value => 'Laptop CLI',
    ]);
});

test('the page renders the nav and the empty state', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settingsCredentials->value)
        ->assertOk()
        ->assertSee('Credentials')
        ->assertSee(Auth::settingsCredentials->value)
        ->assertSee('No tokens yet.');
});

test('the page lists the tokens of the authenticated user only', function (): void {
    $User = User::factory()->createOne();
    $User->createToken('Mine');
    User::factory()->createOne()->createToken('Theirs');

    $this->actingAs($User)
        ->get(Auth::settingsCredentials->value)
        ->assertOk()
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

test('the page shows when a token was last used', function (): void {
    $User = User::factory()->createOne();
    $lastUsedAt = now()->subDay();
    $Token = issuedToken($User, $User->createToken('Laptop CLI'));
    $Token->forceFill([PersonalAccessTokens::last_used_at->value => $lastUsedAt])->save();

    $this->actingAs($User)
        ->get(Auth::settingsCredentials->value)
        ->assertOk()
        ->assertSee('Last Used')
        ->assertSee($lastUsedAt->toFormattedDateString());
});

test('a token is created with only public GET abilities and no expiry', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value, [TokenForm::name => 'Laptop CLI'])
        ->assertRedirect(Auth::settingsCredentials->value)
        ->assertSessionHas('status', 'Token created.')
        ->assertSessionHas(CredentialsTable::sessionKey);

    $Token = $User->tokens()->sole();

    expect($Token->name)->toBe('Laptop CLI')
        ->and($Token->abilities)->toBe([HttpVerb::get->ability(ApiRoute::user->value)])
        ->and($Token->expires_at)->toBeNull();
});

test('an administrator token is created with only GET abilities across every api', function (): void {
    $User = adminUser();

    $this->actingAs($User)
        ->post(Auth::settingsCredentials->value, [TokenForm::name => 'Admin CLI'])
        ->assertSessionHasNoErrors();

    $abilities = $User->tokens()->sole()->abilities ?? [];

    expect($abilities)
        ->toContain(HttpVerb::get->ability(ApiRoute::user->value))
        ->toContain(HttpVerb::get->ability(Admin::api_users->value))
        ->and(array_filter($abilities, static fn (string $ability): bool => str_starts_with($ability, HttpVerb::get->value.HttpVerb::separator)))->toBe($abilities);
});

test('the secret is rendered on the redirect it was flashed to, and never again', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value, [TokenForm::name => 'Laptop CLI'])
        ->assertRedirect(Auth::settingsCredentials->value)
        ->assertSessionHas(CredentialsTable::sessionKey);

    // The plain text secret is the token id, a separator, then the part that is only
    // ever hashed — so the id and separator are enough to find it on the page.
    $secret = $User->tokens()->sole()->id.'|';

    $this->get(Auth::settingsCredentials->value)
        ->assertOk()
        ->assertSee('Copy your new token now.')
        ->assertSee('data-token-dialog', false)
        ->assertSee('data-copy-token', false)
        ->assertSee($secret);

    $this->get(Auth::settingsCredentials->value)
        ->assertOk()
        ->assertDontSee($secret);
});

test('a name is squished before it is stored', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value, [TokenForm::name => '  Laptop   CLI  ']);

    expect($User->tokens()->sole()->name)->toBe('Laptop CLI');
});

test('an expiry is stored when one is given', function (): void {
    $User = User::factory()->createOne();
    $expiry = now()->addMonth()->toDateString();

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value, [
            TokenForm::name => 'Laptop CLI',
            TokenForm::expires_at => $expiry,
        ])
        ->assertSessionHasNoErrors();

    expect($User->tokens()->sole()->expires_at?->toDateString())->toBe($expiry);
});

test('validation fails with a missing name', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value)
        ->assertRedirect(Auth::settingsCredentials->value)
        ->assertSessionHasErrors(TokenForm::name);

    expect($User->tokens()->count())->toBe(0);
});

test('validation fails with an expiry that has passed', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value, [
            TokenForm::name => 'Laptop CLI',
            TokenForm::expires_at => now()->subDay()->toDateString(),
        ])
        ->assertSessionHasErrors(TokenForm::expires_at);

    expect($User->tokens()->count())->toBe(0);
});

test('validation errors are displayed on the form', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsCredentials->value)
        ->followingRedirects()
        ->post(Auth::settingsCredentials->value, [TokenForm::name => ''])
        ->assertOk()
        ->assertSee('The name field is required.');
});

test('old input is preserved on validation failure', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsCredentials->value)
        ->post(Auth::settingsCredentials->value, [TokenForm::name => str_repeat('a', 256)])
        ->assertSessionHasErrors(TokenForm::name)
        ->assertSessionHasInput(TokenForm::name, str_repeat('a', 256));
});

test('a token is revoked', function (): void {
    $User = User::factory()->createOne();
    $Token = issuedToken($User, $User->createToken('Laptop CLI'));

    $this->actingAs($User)
        ->from(Auth::settingsCredentials->value)
        ->delete(Auth::settingsCredential->url([Auth::credentialParameter => $Token->id]))
        ->assertRedirect(Auth::settingsCredentials->value)
        ->assertSessionHas('status', 'Token revoked.');

    expect($User->tokens()->count())->toBe(0);
});

test('a token belonging to somebody else is not found', function (): void {
    $Owner = User::factory()->createOne();
    $Token = issuedToken($Owner, $Owner->createToken('Theirs'));

    $this->actingAs(User::factory()->createOne())
        ->from(Auth::settingsCredentials->value)
        ->delete(Auth::settingsCredential->url([Auth::credentialParameter => $Token->id]))
        ->assertNotFound();

    expect($Owner->tokens()->count())->toBe(1);
});

test('guests cannot revoke a token', function (): void {
    $Owner = User::factory()->createOne();
    $Token = issuedToken($Owner, $Owner->createToken('Laptop CLI'));

    $this->delete(Auth::settingsCredential->url([Auth::credentialParameter => $Token->id]))
        ->assertRedirect(Web::login->value);

    expect($Owner->tokens()->count())->toBe(1);
});
