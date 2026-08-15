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

test('route binding reuses the authenticated user', function (): void {
    $User = User::factory()->createOne();
    $this->actingAs($User);

    expect((new User)->resolveRouteBinding($User->id))->toBe($User);
});

test('route binding resolves another user from the database', function (): void {
    $this->actingAs(User::factory()->createOne());
    $Other = User::factory()->createOne();

    expect((new User)->resolveRouteBinding($Other->id)?->is($Other))->toBeTrue();
});
