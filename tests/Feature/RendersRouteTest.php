<?php

use App\Routes\ApiRoute;
use App\Routes\Web;
use Illuminate\Http\Request;

test('a route is active for itself and its descendants', function (): void {
    expect(Web::login->isActive(Request::create(Web::login->value)))->toBeTrue()
        ->and(Web::login->isActive(Request::create(Web::login->value.'/callback')))->toBeTrue()
        ->and(Web::login->isActive(Request::create(Web::register->value)))->toBeFalse();
});

test('a route is exact only for itself', function (): void {
    expect(ApiRoute::login->isExact(Request::create(ApiRoute::login->value)))->toBeTrue()
        ->and(ApiRoute::login->isExact(Request::create(ApiRoute::login->value.'/callback')))->toBeFalse();
});

test('route parameters are substituted before matching', function (): void {
    $route = ['id' => '1', 'hash' => 'abc'];

    expect(Web::verificationVerify->isExact(Request::create('/email/verify/1/abc'), $route))->toBeTrue()
        ->and(Web::verificationVerify->isActive(Request::create('/email/verify/1/abc'), $route))->toBeTrue();
});
