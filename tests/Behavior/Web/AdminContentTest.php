<?php

use App\Helpers\CacheKey;
use App\Modules\Admin\Content\ContentUpdateRequest;
use App\Routes\Admin;
use App\Routes\ApiRoute;
use App\Routes\Web;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    foreach (CacheKey::cases() as $key) {
        Cache::forget($key->value);
    }
});

afterEach(function (): void {
    foreach (CacheKey::cases() as $key) {
        Cache::forget($key->value);
    }
});

test('only admins can open the site content editor', function (): void {
    $this->get(Admin::content->value)->assertRedirect(Web::login->value);

    $this->actingAs(adminUser())
        ->get(Admin::content->value)
        ->assertOk()
        ->assertSee(CacheKey::robots->value)
        ->assertSee(CacheKey::llms->value)
        ->assertSee(CacheKey::api_readme->value);
});

test('the resource files are the default content', function (): void {
    $this->get(Web::robots->value)->assertSee((string) file_get_contents(resource_path(CacheKey::robots->value)), false);
    $this->get(Web::llms->value)->assertSee((string) file_get_contents(resource_path(CacheKey::llms->value)), false);
    $this->getJson(ApiRoute::readme->value)
        ->assertJsonPath('data.content', (string) file_get_contents(resource_path(CacheKey::api_readme->value)));
});

test('an admin can save all site content in cache', function (): void {
    $content = [
        ContentUpdateRequest::robots => 'User-agent: *\nDisallow: /private',
        ContentUpdateRequest::llms => '# Custom agent guide',
        ContentUpdateRequest::api_readme => '# Custom API guide',
    ];

    $this->actingAs(adminUser())
        ->post(Admin::content->value, $content)
        ->assertRedirect()
        ->assertSessionHas('status', 'Site content updated.');

    expect(Cache::get(CacheKey::robots->value))->toBe($content[ContentUpdateRequest::robots])
        ->and(Cache::get(CacheKey::llms->value))->toBe($content[ContentUpdateRequest::llms])
        ->and(Cache::get(CacheKey::api_readme->value))->toBe($content[ContentUpdateRequest::api_readme]);

    $this->get(Web::robots->value)->assertSee($content[ContentUpdateRequest::robots], false);
    $this->get(Web::llms->value)->assertSee($content[ContentUpdateRequest::llms], false);
    $this->getJson(ApiRoute::readme->value)->assertJsonPath('data.content', $content[ContentUpdateRequest::api_readme]);
});

test('all site content fields are required', function (): void {
    $this->actingAs(adminUser())
        ->post(Admin::content->value)
        ->assertSessionHasErrors([
            ContentUpdateRequest::robots,
            ContentUpdateRequest::llms,
            ContentUpdateRequest::api_readme,
        ]);
});
