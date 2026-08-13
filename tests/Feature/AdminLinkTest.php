<?php

use App\AppConfig;
use App\Routes\AdminLink;
use App\Routes\ApiRoute;
use App\Routes\Auth;
use App\Routes\Web;
use Tests\Fixtures\RouteIndexStub;

/**
 * The order every tagged case asked for, keyed by the url it renders. Read off the
 * indexes rather than restated, so tagging a case is the only place an order is written.
 *
 * @return array<string, int>
 */
function taggedOrders(): array
{
    $orders = [];

    foreach (AppConfig::routeIndexes() as $enum) {
        foreach (AdminLink::links($enum) as $link) {
            $orders[$link[AdminLink::url]] = $link[AdminLink::order];
        }
    }

    return $orders;
}

test('the attribute tags a case', function (): void {
    $attributes = new ReflectionClass(AdminLink::class)->getAttributes(Attribute::class);

    expect($attributes[0]->newInstance()->flags)->toBe(Attribute::TARGET_CLASS_CONSTANT);
});

test('the documents an admin reads are listed', function (): void {
    expect(array_column(AdminLink::routes(), AdminLink::url))->toContain(
        Web::robots->value,
        Web::llms->value,
        Web::sitemap->value,
        Web::openapi->value,
        ApiRoute::readme->value,
    );
});

// Every tagged case is listed once, wherever it was tagged, and an order is what moves it
// up the page: the sequence of orders the page renders never descends.
test('every tagged case is listed once, and the orders ascend', function (): void {
    $orders = taggedOrders();
    $listed = array_column(AdminLink::routes(), AdminLink::url);

    $sequence = array_map(static fn (string $url): int => $orders[$url], $listed);
    $ascending = $sequence;
    sort($ascending);

    expect($listed)->toEqualCanonicalizing(array_keys($orders))
        ->and($listed)->toHaveSameSize($orders)
        ->and($sequence)->toBe($ascending);
});

// The argument is optional, and an absent order is not a first one: the case that gives
// none sorts behind every case that does.
test('a case that gives no order sorts last', function (): void {
    expect(new AdminLink()->order)->toBeNull()
        ->and(AdminLink::links(RouteIndexStub::class))->toBe([[
            AdminLink::order => PHP_INT_MAX,
            AdminLink::name => RouteIndexStub::bare->name,
            AdminLink::url => RouteIndexStub::bare->value,
        ]]);
});

// An enum reports what it holds, in the order it declares it. Sorting is the job of
// AdminLink::routes(), which is the only place every index's links meet.
test('an enum reports its own tagged cases, and one holding none reports none', function (): void {
    $tagged = array_column(AdminLink::links(Web::class), AdminLink::name);

    $declared = array_values(array_filter(
        array_map(static fn (Web $Case): string => $Case->name, Web::cases()),
        static fn (string $name): bool => in_array($name, $tagged, true),
    ));

    expect($tagged)->not->toBeEmpty()
        ->and($tagged)->toBe($declared)
        ->and(AdminLink::links(Auth::class))->toBeEmpty();
});

// A case tagged in an enum RouteIndex does not name is not the application's routing,
// so the page does not display it.
test('a case tagged outside the indexes is not listed', function (): void {
    expect(AdminLink::links(RouteIndexStub::class))->not->toBeEmpty()
        ->and(array_column(AdminLink::routes(), AdminLink::url))
        ->not->toContain(RouteIndexStub::bare->value);
});
