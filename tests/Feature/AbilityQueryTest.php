<?php

use App\Helpers\HttpVerb;
use App\Modules\Api\Support\AbilityQuery;
use App\Routes\Admin;
use App\Routes\ApiRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\Fixtures\NumericApiRoute;

test('a method this api does not answer is held to the ability that reads', function (): void {
    expect(HttpVerb::of(Request::create(ApiRoute::user->value, 'HEAD')))->toBe(HttpVerb::get)
        ->and(HttpVerb::of(Request::create(ApiRoute::user->value, 'PATCH')))->toBe(HttpVerb::patch);
});

test('an ability is one verb and one path, however the path was spelled', function (): void {
    expect(HttpVerb::delete->ability(ApiRoute::user->value))
        ->toBe('DELETE'.HttpVerb::separator.ApiRoute::user->value)
        ->toBe(HttpVerb::delete->ability(ltrim(ApiRoute::user->value, '/')));
});

test('every path it offers is one the api index declares', function (): void {
    foreach (array_keys(AbilityQuery::get()) as $path) {
        expect(ApiRoute::tryFrom($path))->not->toBeNull();
    }
});

test('a path no token is asked for is not one that can be granted', function (): void {
    expect(array_keys(AbilityQuery::get()))
        ->not->toContain(ApiRoute::readme->value, ApiRoute::authenticated->value);
});

test('the verbs of a path are the ones bound to it', function (): void {
    expect(AbilityQuery::get()[ApiRoute::user->value])->toBe([HttpVerb::get]);
});

test('the grantable abilities are every verb of every path it offers', function (): void {
    $abilities = AbilityQuery::abilities();
    $expected = [];

    foreach (AbilityQuery::get() as $path => $verbs) {
        foreach ($verbs as $Verb) {
            $expected[] = $Verb->ability($path);
        }
    }

    expect($abilities)->toBe($expected)
        ->and($abilities)->toContain(HttpVerb::get->ability(ApiRoute::user->value));
});

test('the default abilities contain only GET requests', function (): void {
    $abilities = AbilityQuery::getAbilities();

    expect($abilities)->not->toBeEmpty()
        ->and(array_filter($abilities, static fn (string $ability): bool => str_starts_with($ability, HttpVerb::get->value.HttpVerb::separator)))->toBe($abilities);
});

test('admin api abilities are available only to an administrator', function (): void {
    expect(array_keys(AbilityQuery::groups()))->toBe(['public']);

    $this->actingAs(adminUser());

    expect(array_keys(AbilityQuery::groups()))->toBe(['public', 'admin'])
        ->and(AbilityQuery::groups()['public'])->toHaveKey(ApiRoute::user->value)
        ->and(AbilityQuery::groups()['admin'])->toHaveKey(Admin::api_users->value)
        ->and(AbilityQuery::abilities())->toContain(HttpVerb::get->ability(Admin::api_users->value));
});

test('invalid and non string route indexes publish no credential groups', function (): void {
    Config::set('openapi.schemas', [
        'invalid' => ['route_index' => 'NotAnEnum'],
        'numeric' => ['route_index' => NumericApiRoute::class],
    ]);

    expect(AbilityQuery::groups())->toBe([
        'numeric' => [],
    ]);
});
