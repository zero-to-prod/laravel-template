<?php

use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Config;

test('schema document is served', function (): void {
    $this->assertMatchesSchema($this->getJson(Config::string('openapi.route.uri')))
        ->assertOk()
        ->assertJsonPath('openapi', '3.0.4');
});

test('schema document describes every api endpoint', function (): void {
    $paths = $this->getJson(Config::string('openapi.route.uri'))->assertOk()->json('paths');

    foreach (ApiRoute::cases() as $ApiRoute) {
        expect($paths)->toHaveKey($ApiRoute->value);
    }
});
