<?php

use App\Routes\Admin;
use App\View\DataModels\AdminNav;
use App\View\ViewDirectory;

test('the first entry is the links page', function (): void {
    $items = AdminNav::items();

    expect($items[0]->label)->toBe('Users')
        ->and($items[0]->route)->toBe(Admin::users);
});

test('the users page is listed', function (): void {
    expect(collect(AdminNav::items())->pluck('route')->all())->toContain(Admin::users);
});

test('the log viewer is listed', function (): void {
    expect(collect(AdminNav::items())->pluck('route')->all())->toContain(Admin::logs);
});

test('every entry names an icon that exists', function (): void {
    foreach (AdminNav::items() as $NavItem) {
        expect(ViewDirectory::svg->has($NavItem->icon))->toBeTrue();
    }
});
