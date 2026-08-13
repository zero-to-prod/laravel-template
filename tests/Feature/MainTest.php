<?php

use App\Helpers\Theme;
use App\Models\User;
use App\Sources\Db\App\Users;
use App\View\DataModels\Main;

test('a guest gets no theme, no classnames and no rail', function (): void {
    $Main = Main::from([]);

    expect($Main->classnames)->toBeNull()
        ->and($Main->theme)->toBeNull()
        ->and($Main->leftNav)->toBeFalse()
        ->and($Main->adminNav)->toBeFalse()
        ->and($Main->nav())->toBeFalse();
});

test('classnames is the prop the component passes', function (): void {
    expect(Main::from([Main::classnames => 'bg-base-200'])->classnames)->toBe('bg-base-200');
});

test('the theme is the authenticated user attribute', function (): void {
    $this->actingAs(User::factory()->createOne([Users::theme->value => Theme::dark]));

    expect(Main::from([])->theme)->toBe(Theme::dark->value);
});

test('the auto theme renders no attribute', function (): void {
    $this->actingAs(User::factory()->createOne([Users::theme->value => Theme::auto]));

    expect(Main::from([])->theme)->toBeNull();
});

test('either rail widens the content', function (): void {
    expect(Main::from([Main::leftNav => true])->nav())->toBeTrue()
        ->and(Main::from([Main::adminNav => true])->nav())->toBeTrue();
});
