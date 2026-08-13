<?php

use App\Routes\Admin;
use App\View\DataModels\AdminNav;
use App\View\ViewDirectory;

test('the first entry is the links page', function (): void {
    $items = AdminNav::items();

    expect($items[0]->label)->toBe('Links')
        ->and($items[0]->route)->toBe(Admin::links);
});

test('every entry names an icon that exists', function (): void {
    foreach (AdminNav::items() as $NavItem) {
        expect(ViewDirectory::svg->has($NavItem->icon))->toBeTrue();
    }
});
