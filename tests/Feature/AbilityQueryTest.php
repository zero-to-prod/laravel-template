<?php

use App\Helpers\HttpVerb;
use App\Modules\Api\Support\AbilityQuery;
use App\Routes\ApiRoute;
use Illuminate\Http\Request;

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
