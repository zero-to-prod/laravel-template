<?php

use App\Helpers\Role;
use App\Models\User;
use App\Routes\Admin;
use App\Routes\AdminLink;
use App\Routes\Web;
use App\Sources\Db\App\Roles;

function admin(): User
{
    $User = User::factory()->createOne();
    $User->assignRole(Role::admin->value);

    return $User;
}

test('the migration creates the admin role', function (): void {
    $this->assertDatabaseHas(Roles::table(), [
        Roles::name->value => Role::admin->value,
        Roles::guard_name->value => config('auth.defaults.guard'),
    ]);
});

test('the admin role is the only role', function (): void {
    expect(Role::cases())->toBe([Role::admin]);
});

test('guests are redirected to login', function (): void {
    $this->get(Admin::index->value)
        ->assertRedirect(Web::login->value);
});

test('a registered user holds no role', function (): void {
    expect(User::factory()->createOne()->getRoleNames()->all())->toBeEmpty();
});

test('a user without the admin role is forbidden', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Admin::index->value)
        ->assertForbidden();
});

test('the page renders for a user holding the admin role', function (): void {
    $this->actingAs(admin())
        ->get(Admin::index->value)
        ->assertOk()
        ->assertSee('Admin')
        ->assertSee('Registered users');
});

test('the admin rail replaces the default one, and leads with the links page', function (): void {
    $this->actingAs(admin())
        ->get(Admin::index->value)
        ->assertOk()
        ->assertSee('aria-label="Admin"', false)
        ->assertDontSee('aria-label="Primary"', false)
        ->assertSee('Links')
        ->assertSee(Admin::links->value);
});

test('guests are redirected to login from the links page', function (): void {
    $this->get(Admin::links->value)
        ->assertRedirect(Web::login->value);
});

test('a user without the admin role is forbidden the links page', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Admin::links->value)
        ->assertForbidden();
});

test('the links page lists every marked route', function (): void {
    $TestResponse = $this->actingAs(admin())
        ->get(Admin::links->value)
        ->assertOk()
        ->assertSee('Links');

    foreach (AdminLink::routes() as $link) {
        $TestResponse->assertSee($link[AdminLink::url]);
    }
});

test('the default rail is left alone off the admin pages', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('aria-label="Primary"', false)
        ->assertDontSee('aria-label="Admin"', false);
});

// Some of the links leave the application, where nothing else would notice one that
// stopped resolving.
test('every link the page lists is reachable', function (): void {
    $this->actingAs(admin());

    // A link is broken when it resolves to nothing, which a redirect to another page
    // this application serves is not.
    foreach (AdminLink::routes() as $link) {
        expect($this->get($link[AdminLink::url])->getStatusCode())->toBeLessThan(400);
    }
});
