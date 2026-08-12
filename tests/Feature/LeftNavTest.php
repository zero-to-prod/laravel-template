<?php

use App\Models\User;
use App\Routes\Web;
use App\View\DataModels\LeftNav;

test('the rail links to home and the dashboard', function (): void {
    expect(LeftNav::items())->toHaveCount(2)
        ->and(LeftNav::items()[0]['route'])->toBe(Web::home)
        ->and(LeftNav::items()[1]['route'])->toBe(Web::dashboard);
});

test('the rail is shown to an authenticated user', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee(Web::dashboard->value);
});

test('the rail is hidden on the settings pages, which carry their own nav', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::settingsProfile->value)
        ->assertOk()
        ->assertDontSee(Web::dashboard->value);
});

test('the rail is hidden from a guest', function (): void {
    $this->get(Web::home->value)
        ->assertOk()
        ->assertDontSee(Web::dashboard->value);
});

test('the home case is marked active on the root path', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('menu-active');
});
