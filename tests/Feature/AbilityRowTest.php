<?php

use App\Helpers\HttpVerb;
use App\Routes\ApiRoute;
use App\View\DataModels\AbilityRow;
use Zerotoprod\DataModel\PropertyRequiredException;

/** @param  array<string, mixed>  $overrides */
function abilityRow(array $overrides = []): AbilityRow
{
    return AbilityRow::from([
        AbilityRow::path => ApiRoute::user->value,
        AbilityRow::verbs => [HttpVerb::get, HttpVerb::patch],
        ...$overrides,
    ]);
}

test('the path and the verbs bound to it are required', function (): void {
    AbilityRow::from([AbilityRow::path => ApiRoute::user->value]);
})->throws(PropertyRequiredException::class);

test('a row grants nothing until it is told what is granted', function (): void {
    expect(abilityRow()->granted)->toBeEmpty()
        ->and(abilityRow()->every)->toBeFalse();
});

test('an ability names the verb and the path it reaches', function (): void {
    expect(abilityRow()->ability(HttpVerb::get))->toBe('GET'.HttpVerb::separator.ApiRoute::user->value);
});

test('only a verb bound to the path is offered', function (): void {
    $AbilityRow = abilityRow();

    expect($AbilityRow->bound(HttpVerb::get))->toBeTrue()
        ->and($AbilityRow->bound(HttpVerb::patch))->toBeTrue()
        ->and($AbilityRow->bound(HttpVerb::delete))->toBeFalse();
});

test('a verb is ticked only when the token holds that exact ability', function (): void {
    $AbilityRow = abilityRow([
        AbilityRow::granted => [HttpVerb::get->ability(ApiRoute::user->value)],
    ]);

    expect($AbilityRow->checked(HttpVerb::get))->toBeTrue()
        ->and($AbilityRow->checked(HttpVerb::patch))->toBeFalse();
});

test('a token holding every ability ticks every verb', function (): void {
    $AbilityRow = abilityRow([AbilityRow::every => true]);

    expect($AbilityRow->checked(HttpVerb::get))->toBeTrue()
        ->and($AbilityRow->checked(HttpVerb::delete))->toBeTrue();
});

test('an ability granted on another path does not tick this one', function (): void {
    $AbilityRow = abilityRow([
        AbilityRow::granted => [HttpVerb::get->ability(ApiRoute::cache->value)],
    ]);

    expect($AbilityRow->checked(HttpVerb::get))->toBeFalse();
});
