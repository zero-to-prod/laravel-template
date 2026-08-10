<?php

use App\Helpers\HttpHeader;
use App\Models\User;
use App\Modules\Register\RegisterFormFactory;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('guests are redirected to login when visiting the notice', function (): void {
    $this->get(Web::verificationNotice->value)
        ->assertRedirect(Web::login->value);
});

test('an unverified user can view the notice', function (): void {
    $User = User::factory()->unverified()->createOne();

    $this->actingAs($User)
        ->get(Web::verificationNotice->value)
        ->assertOk();
});

test('a verified user visiting the notice is redirected home', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(Web::verificationNotice->value)
        ->assertRedirect(Web::home->value);
});

test('a verified user reaches a protected route in production', function (): void {
    config(['app.env' => 'production']);
    $User = User::factory()->createOne();

    $this->actingAs($User)
        ->get(Web::dashboard->value)
        ->assertNoContent();
});

test('an unverified user reaches a protected route outside production', function (): void {
    $User = User::factory()->unverified()->createOne();

    $this->actingAs($User)
        ->get(Web::dashboard->value)
        ->assertNoContent();
});

test('an unverified user is redirected to the notice from a protected route in production', function (): void {
    config(['app.env' => 'production']);
    $User = User::factory()->unverified()->createOne();

    $this->actingAs($User)
        ->get(Web::dashboard->value)
        ->assertRedirect(Web::verificationNotice->value);
});

test('an unverified htmx request to a protected route returns a no content response with an hx redirect header in production', function (): void {
    config(['app.env' => 'production']);
    $User = User::factory()->unverified()->createOne();

    $this->actingAs($User)
        ->withHeader(HttpHeader::HxRequest->value, 'true')
        ->get(Web::dashboard->value)
        ->assertNoContent(403)
        ->assertHeader(HttpHeader::HxRedirect->value, Web::verificationNotice->value);
});

test('a valid signed link marks the user as verified', function (): void {
    Event::fake([Verified::class]);
    $User = User::factory()->unverified()->createOne();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $User->getKey(),
        'hash' => sha1($User->getEmailForVerification()),
    ]);

    $this->actingAs($User)
        ->get($url)
        ->assertRedirect(Web::home->value);

    expect($User->refresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

test('an invalid hash is rejected and leaves the user unverified', function (): void {
    $User = User::factory()->unverified()->createOne();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $User->getKey(),
        'hash' => sha1('not-the-right-email'),
    ]);

    $this->actingAs($User)
        ->get($url)
        ->assertForbidden();

    expect($User->refresh()->hasVerifiedEmail())->toBeFalse();
});

test('an unsigned link is rejected', function (): void {
    $User = User::factory()->unverified()->createOne();

    $url = route('verification.verify', [
        'id' => $User->getKey(),
        'hash' => sha1($User->getEmailForVerification()),
    ]);

    $this->actingAs($User)
        ->get($url)
        ->assertForbidden();

    expect($User->refresh()->hasVerifiedEmail())->toBeFalse();
});

test('resending the notification dispatches a new verification email', function (): void {
    Notification::fake();
    $User = User::factory()->unverified()->createOne();

    $this->actingAs($User)
        ->post(Web::verificationSend->value)
        ->assertRedirect()
        ->assertSessionHas('status', 'Verification link sent!');

    Notification::assertSentTo($User, VerifyEmail::class);
});

test('registering sends an email verification notification', function (): void {
    Notification::fake();
    $RegisterForm = RegisterFormFactory::factory()->make();

    $this->post(Web::register->value, $RegisterForm->toArray())
        ->assertRedirect(Web::home->value);

    $User = User::query()->where(Users::email->value, $RegisterForm->email)->firstOrFail();

    Notification::assertSentTo($User, VerifyEmail::class);
    expect($User->hasVerifiedEmail())->toBeFalse();
});
