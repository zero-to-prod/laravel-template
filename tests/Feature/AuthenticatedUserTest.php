<?php

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

test('the authenticated user is returned for an authenticated request', function (): void {
    $ModelUser = User::factory()->createOne();

    $Request = Request::create('/');
    $Request->setUserResolver(fn (): User => $ModelUser);

    expect(User::authenticated($Request))->toBe($ModelUser);
});

test('an unauthenticated request is rejected', function (): void {
    User::authenticated(Request::create('/'));
})->throws(AuthenticationException::class);
