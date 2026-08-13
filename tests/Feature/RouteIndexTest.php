<?php

use App\AppConfig;
use App\Routes\Admin;
use App\Routes\ApiRoute;
use App\Routes\Auth;
use App\Routes\MiddlewareTag;
use App\Routes\RouteIndex;
use App\Routes\Web;
use Tests\Fixtures\RouteIndexStub;

// A case of the registry is the whole of registering an index: an enum it does not name
// is not one, wherever that enum lives.
test('the cases of the registry are the indexes', function (): void {
    expect(AppConfig::routeIndexes())
        ->toContain(Admin::class, ApiRoute::class, Auth::class, Web::class)
        ->and(AppConfig::routeIndexes())
        ->not->toContain(MiddlewareTag::class, RouteIndexStub::class);
});

// The registry is read rather than discovered, so the order it declares its cases in is
// the order the indexes come back in.
test('the indexes come back in the order the registry declares them', function (): void {
    expect(AppConfig::routeIndexes())->toBe(array_column(RouteIndex::cases(), 'value'));
});

test('every index is a string backed enum, which is what makes its cases urls', function (): void {
    foreach (AppConfig::routeIndexes() as $enum) {
        expect(enum_exists($enum))->toBeTrue()
            ->and(new ReflectionEnum($enum)->getBackingType()?->getName())->toBe('string');
    }
});
