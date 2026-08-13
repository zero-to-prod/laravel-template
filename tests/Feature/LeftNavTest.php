<?php

use App\Models\User;
use App\Routes\Auth;
use App\Routes\Web;
use App\View\DataModels\LeftNav;

test('the rail links to home', function (): void {
    expect(LeftNav::items())->toHaveCount(1)
        ->and(LeftNav::items()[0]->route)->toBe(Web::home);
});

test('the rail is shown to an authenticated user', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('lg:pl-56');
});

test('the settings pages carry their own rail instead', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settingsProfile->value)
        ->assertOk()
        ->assertDontSee('aria-label="Primary"', false)
        ->assertSee('aria-label="Settings"', false);
});

test('the rail is hidden from a guest', function (): void {
    $this->get(Web::home->value)
        ->assertOk()
        ->assertDontSee('lg:pl-56');
});

test('the home case is marked active on the root path', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('menu-active');
});
