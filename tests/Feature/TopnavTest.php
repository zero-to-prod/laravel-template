<?php

use App\View\DataModels\AdminNav;
use App\View\DataModels\LeftNav;
use App\View\DataModels\Main;
use App\View\DataModels\SettingsNav;
use App\View\DataModels\Topnav;

test('no rail means no dropdown', function (): void {
    $Topnav = Topnav::from([]);

    expect($Topnav->leftNav)->toBeFalse()
        ->and($Topnav->adminNav)->toBeFalse()
        ->and($Topnav->settingsNav)->toBeFalse()
        ->and($Topnav->nav())->toBeFalse();
});

test('the dropdown mirrors the left rail', function (): void {
    $Topnav = Topnav::from([Topnav::leftNav => true]);

    expect($Topnav->nav())->toBeTrue()
        ->and($Topnav->items())->toEqual(LeftNav::items());
});

test('the admin rail wins the dropdown', function (): void {
    $Topnav = Topnav::from([Topnav::leftNav => true, Topnav::adminNav => true]);

    expect($Topnav->items())->toEqual(AdminNav::items());
});

test('the dropdown mirrors the settings rail', function (): void {
    $Topnav = Topnav::from([Topnav::settingsNav => true]);

    expect($Topnav->nav())->toBeTrue()
        ->and($Topnav->items())->toEqual(SettingsNav::items());
});

test('main projects the props the topnav declares', function (): void {
    $props = Main::from([Main::leftNav => true, Main::adminNav => false, Main::settingsNav => false])->topnav();

    expect($props)->toBe([Topnav::leftNav => true, Topnav::adminNav => false, Topnav::settingsNav => false])
        ->and(Topnav::from($props)->items())->toEqual(LeftNav::items());
});
