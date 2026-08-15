<?php

use App\Models\Session;
use App\Models\User;
use App\Modules\Admin\Sessions\SessionDeleteController;
use App\Routes\Admin;
use App\Routes\Web;
use App\Sources\Db\App\Sessions;
use App\Sources\Db\App\Users;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;

function webSessionGuard(): SessionGuard
{
    $Guard = Auth::guard();

    if (! $Guard instanceof SessionGuard) {
        throw new RuntimeException('The web guard must use sessions.');
    }

    return $Guard;
}

test('the user page links to the users sessions', function (): void {
    $User = User::factory()->createOne();
    $url = Admin::sessions->value.'?'.http_build_query([Admin::userParameter => $User->id]);

    $this->actingAs(adminUser())
        ->get(Admin::user->url([Admin::userParameter => $User->id]))
        ->assertOk()
        ->assertSee($url, false);
});

test('the user page shows the last session time in record details', function (): void {
    $User = User::factory()->createOne();
    $lastSessionAt = now()->subHour()->startOfSecond();
    Session::query()->create([
        Sessions::id->value => 'user-detail-session',
        Sessions::user_id->value => $User->id,
        Sessions::payload->value => 'payload',
        Sessions::last_activity->value => $lastSessionAt->timestamp,
    ]);

    $this->actingAs(adminUser())
        ->get(Admin::user->url([Admin::userParameter => $User->id]))
        ->assertOk()
        ->assertSee('Last session')
        ->assertSee($lastSessionAt->toDayDateTimeString());
});

test('the authenticated user and roles are not queried twice on their own page', function (): void {
    $Admin = adminUser();
    $queries = [];
    Session::resolveConnection()->listen(static function (QueryExecuted $QueryExecuted) use (&$queries): void {
        $queries[] = [$QueryExecuted->sql, $QueryExecuted->bindings];
    });

    $this->withSession([webSessionGuard()->getName() => $Admin->id])
        ->get(Admin::user->url([Admin::userParameter => $Admin->id]))
        ->assertOk();

    expect($queries)
        ->toHaveCount(4)
        ->toHaveSameSize(array_unique(array_map(serialize(...), $queries)));
});

test('an admin can view a users sessions', function (): void {
    $User = User::factory()->createOne();
    Session::query()->create([
        Sessions::id->value => 'managed-web-session',
        Sessions::user_id->value => $User->id,
        Sessions::ip_address->value => '127.0.0.1',
        Sessions::user_agent->value => 'Example Browser',
        Sessions::payload->value => 'private payload',
        Sessions::last_activity->value => now()->timestamp,
    ]);
    $url = Admin::sessions->value.'?'.http_build_query([Admin::userParameter => $User->id]);

    $this->actingAs(adminUser())->get($url)
        ->assertOk()
        ->assertSee('managed-web-session')
        ->assertSee('127.0.0.1')
        ->assertSee('Example Browser')
        ->assertDontSee('private payload');
});

test('guests and non admins cannot view user sessions', function (): void {
    $User = User::factory()->createOne();
    $url = Admin::sessions->value.'?'.http_build_query([Admin::userParameter => $User->id]);

    $this->get($url)->assertRedirect(Web::login->value);
    $this->actingAs(User::factory()->createOne())->get($url)->assertForbidden();
});

test('the sessions navigation page lists sessions across users', function (): void {
    $User = User::factory()->createOne();
    Session::query()->create([
        Sessions::id->value => 'all-users-session',
        Sessions::user_id->value => $User->id,
        Sessions::payload->value => 'private payload',
        Sessions::last_activity->value => now()->timestamp,
    ]);

    $this->actingAs(adminUser())
        ->get(Admin::sessions->value)
        ->assertOk()
        ->assertSee('all-users-session')
        ->assertSee($User->id);
});

test('sessions can be searched by user email', function (): void {
    $MatchingUser = User::factory()->createOne([Users::email->value => 'matching@example.com']);
    $OtherUser = User::factory()->createOne([Users::email->value => 'other@example.com']);

    foreach ([[$MatchingUser, 'matching-session'], [$OtherUser, 'other-session']] as [$User, $sessionId]) {
        Session::query()->create([
            Sessions::id->value => $sessionId,
            Sessions::user_id->value => $User->id,
            Sessions::payload->value => 'payload',
            Sessions::last_activity->value => now()->timestamp,
        ]);
    }

    $this->actingAs(adminUser())
        ->get(Admin::sessions->value.'?'.http_build_query(['email' => 'matching@']))
        ->assertOk()
        ->assertSee('matching-session')
        ->assertSee($MatchingUser->email)
        ->assertDontSee('other-session');
});

test('an admin can revoke an individual session', function (): void {
    $User = User::factory()->createOne([Users::remember_token->value => 'remembered']);
    Session::query()->create([
        Sessions::id->value => 'revoked-session',
        Sessions::user_id->value => $User->id,
        Sessions::payload->value => 'payload',
        Sessions::last_activity->value => now()->timestamp,
    ]);

    $this->actingAs(adminUser())
        ->delete(Admin::session->url([Admin::sessionParameter => 'revoked-session']))
        ->assertRedirect(Admin::sessions->value)
        ->assertSessionHas('status', 'Session revoked.');

    $this->assertDatabaseMissing(Sessions::table(), [Sessions::id->value => 'revoked-session']);
    expect($User->refresh()->remember_token)->toBeNull();
});

test('revoking the current session signs the admin out', function (): void {
    $Admin = adminUser();
    Auth::login($Admin);
    $sessionId = str_repeat('a', 40);
    $Session = new Store('test', new ArraySessionHandler(120));
    $Session->setId($sessionId);
    $Request = Request::create(Admin::sessions->value, 'DELETE');
    $Request->setLaravelSession($Session);
    Session::query()->create([
        Sessions::id->value => $sessionId,
        Sessions::user_id->value => $Admin->id,
        Sessions::payload->value => 'payload',
        Sessions::last_activity->value => now()->timestamp,
    ]);

    $Response = app(SessionDeleteController::class)($Request, $sessionId);

    expect($Response->getTargetUrl())->toBe(url(Web::login->value));
    $this->assertGuest();
});

test('a missing session cannot be revoked', function (): void {
    $this->actingAs(adminUser())
        ->delete(Admin::session->url([Admin::sessionParameter => 'missing-session']))
        ->assertNotFound();
});

test('an admin can clear every session for one user', function (): void {
    $User = User::factory()->createOne([Users::remember_token->value => 'remembered']);
    $OtherUser = User::factory()->createOne([Users::remember_token->value => 'still-remembered']);
    foreach ([[$User, 'cleared-session'], [$OtherUser, 'retained-session']] as [$SessionUser, $sessionId]) {
        Session::query()->create([
            Sessions::id->value => $sessionId,
            Sessions::user_id->value => $SessionUser->id,
            Sessions::payload->value => 'payload',
            Sessions::last_activity->value => now()->timestamp,
        ]);
    }
    $url = Admin::sessions->value.'?'.http_build_query([Admin::userParameter => $User->id]);

    $this->actingAs(adminUser())
        ->delete(Admin::sessions->value, [Admin::userParameter => $User->id])
        ->assertRedirect($url)
        ->assertSessionHas('status', 'All user sessions cleared.');

    $this->assertAuthenticated();
    $this->assertDatabaseMissing(Sessions::table(), [Sessions::id->value => 'cleared-session']);
    $this->assertDatabaseHas(Sessions::table(), [Sessions::id->value => 'retained-session']);
    expect($User->refresh()->remember_token)->toBeNull()
        ->and($OtherUser->refresh()->remember_token)->toBe('still-remembered');
});

test('clearing sessions requires a user scope', function (): void {
    $Admin = adminUser();

    $this->actingAs($Admin)
        ->get(Admin::sessions->value)
        ->assertOk()
        ->assertDontSee('Clear all sessions');
    $this->actingAs($Admin)
        ->get(Admin::sessions->value.'?'.http_build_query([Admin::userParameter => $Admin->id]))
        ->assertOk()
        ->assertSee('Clear all sessions');
    $this->actingAs($Admin)->delete(Admin::sessions->value)->assertNotFound();
});

test('clearing their own sessions signs the admin out', function (): void {
    $Admin = adminUser();

    $this->actingAs($Admin)
        ->delete(Admin::sessions->value, [Admin::userParameter => $Admin->id])
        ->assertRedirect(Web::login->value)
        ->assertSessionHas('status', 'All user sessions cleared.');

    $this->assertGuest();
});

test('guests and non admins cannot revoke sessions', function (): void {
    $url = Admin::session->url([Admin::sessionParameter => 'protected-session']);

    $this->delete($url)->assertRedirect(Web::login->value);
    $this->actingAs(User::factory()->createOne())->delete($url)->assertForbidden();
    $this->actingAs(User::factory()->createOne())->delete(Admin::sessions->value)->assertForbidden();
});
