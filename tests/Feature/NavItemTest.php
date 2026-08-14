<?php

use App\Helpers\SvgName;
use App\Routes\Auth;
use App\Routes\Web;
use App\View\DataModels\NavItem;
use App\View\DataModels\Svg;
use Illuminate\Http\Request;
use Zerotoprod\DataModel\PropertyRequiredException;

test('an entry carries its label, icon and route case', function (): void {
    $NavItem = NavItem::from([
        NavItem::label => 'Home',
        NavItem::icon => SvgName::home,
        NavItem::route => Web::home,
    ]);

    expect($NavItem->label)->toBe('Home')
        ->and($NavItem->icon)->toBe(SvgName::home)
        ->and($NavItem->route)->toBe(Web::home)
        ->and($NavItem->url())->toBe(Web::home->url());
});

test('every property is required', function (): void {
    NavItem::from([NavItem::label => 'Home', NavItem::icon => SvgName::home]);
})->throws(PropertyRequiredException::class);

test('an entry projects its icon props', function (): void {
    $Svg = Svg::from(NavItem::from([
        NavItem::label => 'Home',
        NavItem::icon => SvgName::home,
        NavItem::route => Web::home,
    ])->svg());

    expect($Svg->name)->toBe(SvgName::home)
        ->and($Svg->classname)->toBe('h-4 w-4 opacity-70');
});

test('an entry is active only on its own path', function (): void {
    $NavItem = NavItem::from([
        NavItem::label => 'Home',
        NavItem::icon => SvgName::home,
        NavItem::route => Web::home,
    ]);

    $this->get(Web::home->value)->assertOk();

    expect($NavItem->nested)->toBeFalse()
        ->and($NavItem->active())->toBeTrue();

    $this->get(Web::contact->value)->assertOk();

    expect($NavItem->active())->toBeFalse();
});

test('a nested entry stays active below its own path', function (): void {
    $NavItem = NavItem::from([
        NavItem::label => 'Credentials',
        NavItem::icon => SvgName::command_line,
        NavItem::route => Auth::settingsCredentials,
        NavItem::nested => true,
    ]);

    app()->instance('request', Request::create(Auth::settingsCredential->url([Auth::credentialParameter => 'abc'])));

    expect($NavItem->active())->toBeTrue();

    app()->instance('request', Request::create(Auth::settingsProfile->value));

    expect($NavItem->active())->toBeFalse();
});
