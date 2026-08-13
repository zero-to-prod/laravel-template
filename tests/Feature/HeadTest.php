<?php

use App\Helpers\Theme;
use App\Models\User;
use App\Routes\Auth;
use App\Routes\Web;
use Illuminate\Support\Facades\Config;

test('the layout renders the site defaults', function (): void {
    $name = Config::string('app.name');

    $this->get(Web::home->value)
        ->assertOk()
        ->assertSee("<title>$name</title>", false)
        ->assertSee('<meta name="description" content="An opinionated Laravel template.">', false)
        ->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0">', false)
        ->assertSee('<meta name="robots" content="all">', false)
        ->assertSee("<meta property=\"og:site_name\" content=\"$name\">", false)
        ->assertSee('<meta name="twitter:card" content="summary">', false)
        ->assertSee('<link rel="canonical"', false);
});

test('the layout renders a theme color for each declared theme', function (): void {
    $this->get(Web::home->value)
        ->assertOk()
        ->assertSee('content="'.Theme::light->color().'" media="(prefers-color-scheme: light)"', false)
        ->assertSee('content="'.Theme::dark->color().'" media="(prefers-color-scheme: dark)"', false);
});

test('a page title is suffixed with the application name', function (): void {
    $name = Config::string('app.name');

    $this->get(Web::login->value)
        ->assertOk()
        ->assertSee("<title>Login - $name</title>", false);
});

test('a page description replaces the default', function (): void {
    $this->get(Web::register->value)
        ->assertOk()
        ->assertSee('<meta name="description" content="Create your account.">', false);
});

test('the document title and description fill the open graph tags', function (): void {
    $name = Config::string('app.name');

    $this->get(Web::register->value)
        ->assertOk()
        ->assertSee("<meta property=\"og:title\" content=\"Register - $name\">", false)
        ->assertSee('<meta property="og:description" content="Create your account.">', false);
});

test('the settings pages are hidden from robots', function (): void {
    $name = Config::string('app.name');

    $this->actingAs(User::factory()->createOne())
        ->get(Auth::settingsAppearance->value)
        ->assertOk()
        ->assertSee("<title>Appearance - $name</title>", false)
        ->assertSee('<meta name="robots" content="none">', false);
});
