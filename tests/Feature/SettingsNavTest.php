<?php

use App\Models\User;
use App\Routes\Auth;
use App\Routes\Web;
use App\View\DataModels\NavItem;
use App\View\DataModels\SettingsNav;
use App\View\ViewDirectory;
use Illuminate\Http\Request;

test('the first entry is the profile page', function (): void {
    $items = SettingsNav::items();

    expect($items[0]->label)->toBe('Profile')
        ->and($items[0]->route)->toBe(Auth::settingsProfile);
});

test('every case describes one navigation item', function (): void {
    foreach (SettingsNav::cases() as $SettingsNav) {
        expect($SettingsNav->item())->toBeInstanceOf(NavItem::class);
    }
});

test('a settings navigation case must describe an item with named attributes', function (mixed $item, string $message): void {
    expect(static fn (): mixed => new ReflectionMethod(SettingsNav::class, 'attributes')->invoke(null, $item))
        ->toThrow(LogicException::class, $message);
})->with([
    'missing item' => [null, 'Settings navigation cases must describe a navigation item.'],
    'positional attribute' => [[Auth::settingsProfile], 'Settings navigation attributes must be named.'],
]);

test('every settings section is listed', function (): void {
    expect(collect(SettingsNav::items())->pluck('route')->all())
        ->toBe([
            Auth::settingsProfile,
            Auth::settingsAppearance,
            Auth::settingsSecurity,
            Auth::settingsCredentials,
            Auth::settingsSessions,
        ]);
});

test('every entry names an icon that exists', function (): void {
    foreach (SettingsNav::items() as $NavItem) {
        expect(ViewDirectory::svg->has($NavItem->icon))->toBeTrue();
    }
});

test('the rail is shown on a settings page', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settingsProfile->value)
        ->assertOk()
        ->assertSee('lg:pl-56')
        ->assertSee('aria-label="Settings"', false);
});

test('the rail is absent everywhere else', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::home->value)
        ->assertOk()
        ->assertDontSee('aria-label="Settings"', false);
});

test('the credentials section stays active below its own path', function (): void {
    app()->instance('request', Request::create(Auth::settingsCredential->url([Auth::credentialParameter => 'abc'])));

    $active = [];

    foreach (SettingsNav::items() as $NavItem) {
        $active[$NavItem->label] = $NavItem->active();
    }

    expect($active['Credentials'])->toBeTrue()
        ->and($active['Profile'])->toBeFalse();
});

test('the sessions section stays active below its own path', function (): void {
    app()->instance('request', Request::create(Auth::settingsSession->url([Auth::sessionParameter => 'abc'])));

    $active = [];

    foreach (SettingsNav::items() as $NavItem) {
        $active[$NavItem->label] = $NavItem->active();
    }

    expect($active['Sessions'])->toBeTrue()
        ->and($active['Profile'])->toBeFalse();
});

test('the section is marked active on its own path', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settingsAppearance->value)
        ->assertOk()
        ->assertSee('menu-active');
});
