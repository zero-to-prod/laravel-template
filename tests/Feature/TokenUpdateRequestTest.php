<?php

use App\Helpers\HttpVerb;
use App\Modules\Settings\Credentials\TokenUpdateRequest;
use App\Routes\ApiRoute;

test('nothing submitted grants nothing', function (): void {
    expect(TokenUpdateRequest::from()->abilities)->toBeEmpty()
        ->and(TokenUpdateRequest::from([TokenUpdateRequest::abilities => []])->abilities)->toBeEmpty();
});

test('a submitted ability this api answers is kept', function (): void {
    $ability = HttpVerb::get->ability(ApiRoute::user->value);

    expect(TokenUpdateRequest::from([TokenUpdateRequest::abilities => [$ability]])->abilities)
        ->toBe([$ability]);
});

test('an ability no endpoint is gated by is dropped', function (): void {
    $ability = HttpVerb::get->ability(ApiRoute::user->value);

    expect(TokenUpdateRequest::from([
        TokenUpdateRequest::abilities => [$ability, HttpVerb::every, 'PUT'.HttpVerb::separator.'/api/nowhere'],
    ])->abilities)->toBe([$ability]);
});

test('a verb the path does not answer is dropped', function (): void {
    expect(TokenUpdateRequest::from([
        TokenUpdateRequest::abilities => [HttpVerb::put->ability(ApiRoute::user->value)],
    ])->abilities)->toBeEmpty();
});

test('anything that is not a list of strings grants nothing', function (): void {
    expect(TokenUpdateRequest::from([TokenUpdateRequest::abilities => 'GET:/api/user'])->abilities)->toBeEmpty()
        ->and(TokenUpdateRequest::from([TokenUpdateRequest::abilities => [['nested']]])->abilities)->toBeEmpty();
});
