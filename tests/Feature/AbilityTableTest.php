<?php

use App\Helpers\HttpVerb;
use App\Modules\Api\Support\AbilityQuery;
use App\Modules\Settings\Credentials\TokenUpdateRequest;
use App\Routes\ApiRoute;
use App\Routes\Auth;
use App\View\DataModels\AbilityRow;
use App\View\DataModels\AbilityTable;
use Zerotoprod\DataModel\PropertyRequiredException;

/** @param  array<string, mixed>  $overrides */
function abilityTable(array $overrides = []): AbilityTable
{
    return AbilityTable::from([
        AbilityTable::id => '01JZZZZZZZZZZZZZZZZZZZZZZZ',
        AbilityTable::name => 'Laptop CLI',
        ...$overrides,
    ]);
}

test('the token it manages is required', function (): void {
    AbilityTable::from([AbilityTable::name => 'Laptop CLI']);
})->throws(PropertyRequiredException::class);

test('a token is read as granting nothing until it is told otherwise', function (): void {
    expect(abilityTable()->granted)->toBeEmpty()
        ->and(abilityTable()->every())->toBeFalse();
});

test('the wildcard is read as every ability', function (): void {
    expect(abilityTable([AbilityTable::granted => [HttpVerb::every]])->every())->toBeTrue();
});

test('the columns are every verb a token can be granted', function (): void {
    expect(abilityTable()->verbs())->toBe(HttpVerb::cases());
});

test('the rows are the token-guarded paths, in the order the index declares them', function (): void {
    $paths = array_map(static fn (AbilityRow $Row): string => $Row->path, abilityTable()->rows());

    expect($paths)->toBe(array_keys(AbilityQuery::get()))
        ->and($paths)->toContain(ApiRoute::user->value);
});

test('a path reached without a token is never offered', function (): void {
    $paths = array_map(static fn (AbilityRow $Row): string => $Row->path, abilityTable()->rows());

    expect($paths)->not->toContain(ApiRoute::readme->value)
        ->and($paths)->not->toContain(ApiRoute::authenticated->value);
});

test('what the token holds is handed to every row', function (): void {
    $granted = [HttpVerb::get->ability(ApiRoute::user->value)];
    $rows = abilityTable([AbilityTable::granted => $granted])->rows();

    foreach ($rows as $Row) {
        expect($Row->granted)->toBe($granted)
            ->and($Row->every)->toBeFalse();
    }
});

test('a wildcard token ticks every row', function (): void {
    foreach (abilityTable([AbilityTable::granted => [HttpVerb::every]])->rows() as $Row) {
        expect($Row->every)->toBeTrue();
    }
});

test('the form posts back to the token it manages', function (): void {
    expect(abilityTable()->action())
        ->toBe(Auth::settingsCredential->url([Auth::credentialParameter => abilityTable()->id]));
});

test('the checkbox name is the key the request reads', function (): void {
    expect(AbilityTable::field)->toBe(TokenUpdateRequest::abilities.'[]');
});
