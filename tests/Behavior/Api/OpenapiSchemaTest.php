<?php

use App\Routes\ApiRoute;
use App\Routes\Web;
use Illuminate\Support\Facades\Config;

test('the enum case is the uri the package is configured to serve', function (): void {
    expect(Web::openapi->value)->toBe('/'.ltrim(Config::string('openapi.schemas.public.route.uri'), '/'));
});

test('schema document is served', function (): void {
    $this->getJson(Config::string('openapi.schemas.public.route.uri'))
        ->assertOk()
        ->assertJsonPath('openapi', '3.0.4')
        ->assertJsonStructure(['info', 'paths']);
});

test('schema document describes every api endpoint', function (): void {
    $paths = $this->getJson(Config::string('openapi.schemas.public.route.uri'))->assertOk()->json('paths');

    foreach (ApiRoute::cases() as $ApiRoute) {
        expect($paths)->toHaveKey($ApiRoute->value);
    }
});
