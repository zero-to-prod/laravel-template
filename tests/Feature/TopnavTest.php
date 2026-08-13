<?php

use App\View\DataModels\AdminNav;
use App\View\DataModels\LeftNav;
use App\View\DataModels\Main;
use App\View\DataModels\Topnav;

test('no rail means no dropdown', function (): void {
    $Topnav = Topnav::from([]);

    expect($Topnav->leftNav)->toBeFalse()
        ->and($Topnav->adminNav)->toBeFalse()
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

test('main projects the props the topnav declares', function (): void {
    $props = Main::from([Main::leftNav => true, Main::adminNav => false])->topnav();

    expect($props)->toBe([Topnav::leftNav => true, Topnav::adminNav => false])
        ->and(Topnav::from($props)->items())->toEqual(LeftNav::items());
});
