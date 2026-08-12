<?php

use App\Models\User;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use App\View\DataModels\UserMenu;

test('initials are taken from the first and last word of the name', function (string $name, string $initials): void {
    expect(UserMenu::from([UserMenu::name => $name])->initials())->toBe($initials);
})->with([
    'first and last' => ['John Doe', 'JD'],
    'a middle name is skipped' => ['John Quincy Doe', 'JD'],
    'a single word' => ['Prince', 'P'],
    'extra whitespace' => ['  john   doe  ', 'JD'],
    'no name' => ['', '?'],
    'only whitespace' => ['   ', '?'],
]);

test('the menu links to settings and logout', function (): void {
    expect(UserMenu::items())->toHaveCount(2)
        ->and(UserMenu::items()[0]['route'])->toBe(Web::settingsProfile)
        ->and(UserMenu::items()[1]['route'])->toBe(Web::logout);
});

test('the topnav shows the account dropdown to an authenticated user', function (): void {
    $User = User::factory()->createOne([
        Users::name->value => 'John Doe',
        Users::email->value => 'john@example.com',
    ]);

    $this->actingAs($User)
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('JD')
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee(Web::settingsProfile->value)
        ->assertSee(Web::logout->value);
});

test('the topnav shows a login link to a guest', function (): void {
    $this->get(Web::home->value)
        ->assertOk()
        ->assertSee(Web::login->value)
        ->assertDontSee(Web::logout->value);
});
