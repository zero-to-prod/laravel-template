<?php

use App\Helpers\Theme;
use App\Models\User;
use App\Modules\Settings\Appearance\AppearanceRequest;
use App\Routes\Web;
use App\Sources\Db\App\Users;

test('guests are redirected to login', function (): void {
    $this->get(Web::settingsAppearance->value)
        ->assertRedirect(Web::login->value);
});

test('guests cannot update a theme', function (): void {
    $this->post(Web::settingsAppearance->value, [AppearanceRequest::theme => Theme::dark->value])
        ->assertRedirect(Web::login->value);
});

test('the page lists every theme', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Web::settingsAppearance->value)
        ->assertOk()
        ->assertSee('Appearance')
        ->assertSee('Light')
        ->assertSee('Dark')
        ->assertSee('Auto')
        ->assertSee('Match the theme your device is set to.');
});

test('a new user starts on the auto theme', function (): void {
    expect(User::factory()->createOne()->theme)->toBe(Theme::auto);
});

test('a theme is selected', function (Theme $Theme): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->from(Web::settingsAppearance->value)
        ->post(Web::settingsAppearance->value, [AppearanceRequest::theme => $Theme->value])
        ->assertRedirect(Web::settingsAppearance->value)
        ->assertSessionHas('status', 'Appearance updated.');

    expect($User->refresh()->theme)->toBe($Theme);
})->with([
    'light' => [Theme::light],
    'dark' => [Theme::dark],
    'auto' => [Theme::auto],
]);

test('the status toast carries a dismiss control', function (): void {
    $content = (string) $this->actingAs(User::factory()->createOne())
        ->from(Web::settingsAppearance->value)
        ->followingRedirects()
        ->post(Web::settingsAppearance->value, [AppearanceRequest::theme => Theme::dark->value])
        ->assertOk()
        ->assertSee('Appearance updated.')
        ->getContent();

    expect($content)->toContain('data-toast')
        ->and($content)->toContain('data-dismiss-toast')
        ->and($content)->toContain('aria-label="Dismiss"');
});

test('the selected theme is the checked radio when the page is shown again', function (): void {
    $content = (string) $this->actingAs(User::factory()->createOne([Users::theme->value => Theme::dark]))
        ->get(Web::settingsAppearance->value)
        ->assertOk()
        ->getContent();

    expect($content)->toMatch('/value="dark"[^>]*checked/')
        ->and($content)->not->toMatch('/value="light"[^>]*checked/');
});

test('validation fails with an unknown theme', function (): void {
    $User = User::factory()->createOne([Users::theme->value => Theme::light]);

    $this->actingAs($User)
        ->from(Web::settingsAppearance->value)
        ->post(Web::settingsAppearance->value, [AppearanceRequest::theme => 'solarized'])
        ->assertSessionHasErrors(AppearanceRequest::theme);

    expect($User->refresh()->theme)->toBe(Theme::light);
});

test('validation fails with a missing theme', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->from(Web::settingsAppearance->value)
        ->post(Web::settingsAppearance->value)
        ->assertSessionHasErrors(AppearanceRequest::theme);
});

test('an explicit theme is rendered on the document', function (Theme $Theme, string $expected): void {
    $User = User::factory()->createOne([Users::theme->value => $Theme]);

    $this->actingAs($User)
        ->get(Web::home->value)
        ->assertOk()
        ->assertSee($expected, false);
})->with([
    'light' => [Theme::light, '<html lang="en" data-theme="light"'],
    'dark' => [Theme::dark, '<html lang="en" data-theme="dark"'],
]);

test('the auto theme leaves the attribute off so the device decides', function (): void {
    $User = User::factory()->createOne([Users::theme->value => Theme::auto]);

    $this->actingAs($User)
        ->get(Web::home->value)
        ->assertOk()
        ->assertDontSee('data-theme', false);
});

test('a guest is served the auto theme', function (): void {
    $this->get(Web::home->value)
        ->assertOk()
        ->assertDontSee('data-theme', false);
});
