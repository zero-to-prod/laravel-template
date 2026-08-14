<?php

use App\Helpers\Role;
use App\Models\User;
use App\Routes\Admin;
use App\Routes\Auth;
use App\Routes\Web;
use App\Sources\Db\App\OauthProviders;
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
        ->and(UserMenu::items()[0]->route)->toBe(Auth::settingsProfile)
        ->and(UserMenu::items()[1]->route)->toBe(Web::logout);
});

test('the menu links an admin to the admin pages', function (): void {
    $this->actingAs(User::factory()->createOne()->assignRole(Role::admin->value));

    expect(UserMenu::items())->toHaveCount(3)
        ->and(UserMenu::items()[0]->route)->toBe(Admin::index);
});

test('the dropdown shows the admin link only to an admin', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(Web::home->value)
        ->assertOk()
        ->assertDontSee(Admin::index->value);

    $User->assignRole(Role::admin->value);

    $this->actingAs($User)
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee(Admin::index->value);
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
        ->assertSee(Auth::settingsProfile->value)
        ->assertSee(Web::logout->value);
});

test('the topnav uses the oauth provider picture as the avatar', function (): void {
    $User = User::factory()->createOne([
        Users::name->value => 'John Doe',
    ]);
    $User->oauthProviders()->create([
        OauthProviders::sub->value => '123456789',
        OauthProviders::name->value => 'John Doe',
        OauthProviders::given_name->value => 'John',
        OauthProviders::family_name->value => 'Doe',
        OauthProviders::picture->value => 'https://example.com/avatar.jpg',
        OauthProviders::email->value => $User->email,
        OauthProviders::email_verified->value => true,
        OauthProviders::id->value => '123456789',
        OauthProviders::verified_email->value => true,
    ]);

    $this->actingAs($User)
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee('https://example.com/avatar.jpg')
        ->assertDontSee('JD');
});

test('the topnav shows a login link to a guest', function (): void {
    $this->get(Web::home->value)
        ->assertOk()
        ->assertSee(Web::login->value)
        ->assertDontSee(Web::logout->value);
});
