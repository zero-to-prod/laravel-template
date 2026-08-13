<?php

use App\Routes\Web;
use App\View\DataModels\NavItem;
use App\View\DataModels\Svg;
use Zerotoprod\DataModel\PropertyRequiredException;

test('an entry carries its label, icon and route case', function (): void {
    $NavItem = NavItem::from([
        NavItem::label => 'Home',
        NavItem::icon => 'home',
        NavItem::route => Web::home,
    ]);

    expect($NavItem->label)->toBe('Home')
        ->and($NavItem->icon)->toBe('home')
        ->and($NavItem->route)->toBe(Web::home)
        ->and($NavItem->url())->toBe(Web::home->url());
});

test('every property is required', function (): void {
    NavItem::from([NavItem::label => 'Home', NavItem::icon => 'home']);
})->throws(PropertyRequiredException::class);

test('an entry projects its icon props', function (): void {
    $Svg = Svg::from(NavItem::from([
        NavItem::label => 'Home',
        NavItem::icon => 'home',
        NavItem::route => Web::home,
    ])->svg());

    expect($Svg->name)->toBe('home')
        ->and($Svg->classname)->toBe('h-4 w-4 opacity-70');
});

test('an entry is active only on its own path', function (): void {
    $NavItem = NavItem::from([
        NavItem::label => 'Home',
        NavItem::icon => 'home',
        NavItem::route => Web::home,
    ]);

    $this->get(Web::home->value)->assertOk();

    expect($NavItem->active())->toBeTrue();

    $this->get(Web::contact->value)->assertOk();

    expect($NavItem->active())->toBeFalse();
});
