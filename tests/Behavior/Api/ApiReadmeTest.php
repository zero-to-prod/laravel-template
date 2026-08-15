<?php

use App\Helpers\CacheKey;
use App\Modules\Api\Public\Readme\ReadmeResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\ApiRoute;

test('readme is served without a token', function (): void {
    $this->assertMatchesSchema($this->getJson(ApiRoute::readme->value))
        ->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::message => class_basename(ReadmeResponse::class),
            ApiResponse::type => class_basename(ReadmeResponse::class),
        ])
        ->assertJsonPath(
            ApiResponse::data.'.'.ReadmeResponse::content,
            (string) file_get_contents(resource_path(CacheKey::api_readme->value))
        );
});

test('the readme points to the current API contract', function (): void {
    $readme = (string) file_get_contents(resource_path(CacheKey::api_readme->value));

    expect($readme)->toContain('/openapi.json');
});
