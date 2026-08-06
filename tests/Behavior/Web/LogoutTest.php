<?php

use App\Models\User;
use App\Routes\Web;

test('route is accessible', function (): void {
    $this->get(Web::logout->value)->assertRedirect(Web::home->value);
});

test('authenticated user can logout', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(Web::logout->value)
        ->assertRedirect(Web::home->value);

    $this->assertGuest();
});

test('session is invalidated after logout', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sessionId = session()->getId();

    $this->get(Web::logout->value);

    $this->assertGuest();
    expect(session()->getId())->not->toBe($sessionId);
});

test('guest user is redirected to home', function (): void {
    $this->get(Web::logout->value)
        ->assertRedirect(Web::home->value);

    $this->assertGuest();
});

test('session token is regenerated after logout', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $oldToken = session()->token();

    $this->get(Web::logout->value);

    expect(session()->token())->not->toBe($oldToken);
});
