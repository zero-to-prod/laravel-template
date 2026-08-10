<?php

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

test('the authenticated user is returned for an authenticated request', function (): void {
    $User = User::factory()->createOne();

    $Request = Request::create('/');
    $Request->setUserResolver(fn (): User => $User);

    expect(User::authenticated($Request))->toBe($User);
});

test('an unauthenticated request is rejected', function (): void {
    User::authenticated(Request::create('/'));
})->throws(AuthenticationException::class);
