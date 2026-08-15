<?php

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
            (string) file_get_contents(resource_path('api-readme.md'))
        );
});

// The readme tours the endpoints by path, so a new route silently makes it wrong. The
// enum is the only place a path is spelled, which makes it the thing to check against.
test('the readme mentions every api path', function (): void {
    $readme = (string) file_get_contents(resource_path('api-readme.md'));

    $missing = array_values(array_filter(
        ApiRoute::cases(),
        static fn (ApiRoute $Route): bool => ! str_contains($readme, $Route->value),
    ));

    expect(array_map(static fn (ApiRoute $Route): string => $Route->value, $missing))->toBeArray();
});
