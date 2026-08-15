<?php

use App\Models\Session;
use App\Models\User;
use App\Modules\Settings\Profile\ProfileForm;
use App\Modules\Settings\Sessions\SessionDestroyController;
use App\Routes\Auth;
use App\Routes\Web;
use App\Sources\Db\App\Sessions;
use App\Sources\Db\App\Users;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth as AuthFacade;

function profileSession(User $User, string $id, int|float|string $lastActivity, string $ip = '127.0.0.1'): void
{
    Session::query()->create([
        Sessions::id->value => $id,
        Sessions::user_id->value => $User->id,
        Sessions::ip_address->value => $ip,
        Sessions::user_agent->value => 'Example Browser',
        Sessions::payload->value => 'private payload',
        Sessions::last_activity->value => $lastActivity,
    ]);
}

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

test('the page lists only the authenticated users sessions', function (): void {
    $User = User::factory()->createOne();
    $Other = User::factory()->createOne();
    $lastActivity = now()->subHour()->startOfSecond();
    profileSession($User, 'owned-profile-session', $lastActivity->timestamp);
    profileSession($Other, 'other-profile-session', now()->timestamp, '192.0.2.1');

    $this->actingAs($User)
        ->get(Auth::settingsSessions->value)
        ->assertOk()
        ->assertSee('Sessions')
        ->assertSee('owned-profile-session')
        ->assertSee($lastActivity->toDayDateTimeString())
        ->assertSee('127.0.0.1')
        ->assertSee('Example Browser')
        ->assertDontSee('other-profile-session')
        ->assertDontSee('192.0.2.1')
        ->assertDontSee('private payload');
});

test('a user can revoke one of their sessions', function (): void {
    $User = User::factory()->createOne([Users::remember_token->value => 'remembered']);
    profileSession($User, 'revoked-profile-session', now()->timestamp);

    $this->actingAs($User)
        ->delete(Auth::settingsSession->url([Auth::sessionParameter => 'revoked-profile-session']))
        ->assertRedirect(Auth::settingsSessions->value)
        ->assertSessionHas('status', 'Session revoked.');

    $this->assertDatabaseMissing(Sessions::table(), [Sessions::id->value => 'revoked-profile-session']);
    expect($User->fresh()?->remember_token)->toBeNull();
    $this->assertAuthenticatedAs($User);
});

test('a user cannot revoke another users session', function (): void {
    $User = User::factory()->createOne();
    $Other = User::factory()->createOne();
    profileSession($Other, 'protected-profile-session', now()->timestamp);

    $this->actingAs($User)
        ->delete(Auth::settingsSession->url([Auth::sessionParameter => 'protected-profile-session']))
        ->assertNotFound();

    $this->assertDatabaseHas(Sessions::table(), [Sessions::id->value => 'protected-profile-session']);
});

test('revoking the current session signs the user out', function (): void {
    $User = User::factory()->createOne();
    AuthFacade::login($User);
    $sessionId = str_repeat('a', 40);
    $Session = new Store('test', new ArraySessionHandler(120));
    $Session->setId($sessionId);
    $Request = Request::create(Auth::settingsSessions->value, 'DELETE');
    $Request->setLaravelSession($Session);
    $Request->setUserResolver(static fn (): User => $User);
    profileSession($User, $sessionId, now()->timestamp);

    $Response = app(SessionDestroyController::class)($Request, $sessionId);

    expect($Response->getTargetUrl())->toBe(url(Web::login->value));
    $this->assertGuest();
});

test('a user can clear all of their sessions', function (): void {
    $User = User::factory()->createOne([Users::remember_token->value => 'remembered']);
    $Other = User::factory()->createOne();
    profileSession($User, 'first-profile-session', now()->timestamp);
    profileSession($User, 'second-profile-session', now()->subMinute()->timestamp);
    profileSession($Other, 'retained-profile-session', now()->timestamp);

    $this->actingAs($User)
        ->delete(Auth::settingsSessions->value)
        ->assertRedirect(Web::login->value)
        ->assertSessionHas('status', 'All sessions cleared.');

    $this->assertDatabaseMissing(Sessions::table(), [Sessions::user_id->value => $User->id]);
    $this->assertDatabaseHas(Sessions::table(), [Sessions::id->value => 'retained-profile-session']);
    expect($User->fresh()?->remember_token)->toBeNull();
    $this->assertGuest();
});

test('guests cannot manage settings sessions', function (): void {
    $this->get(Auth::settingsSessions->value)->assertRedirect(Web::login->value);
    $this->delete(Auth::settingsSessions->value)->assertRedirect(Web::login->value);
    $this->delete(Auth::settingsSession->url([Auth::sessionParameter => 'session']))->assertRedirect(Web::login->value);
});

test('an unverified user is redirected to the verification notice', function (): void {
    $this->actingAs(User::factory()->unverified()->createOne())
        ->get(Auth::settingsProfile->value)
        ->assertRedirect(Auth::verificationNotice->value);
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
