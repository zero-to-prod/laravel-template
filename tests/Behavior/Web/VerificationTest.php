<?php

use App\DataModels\User;
use App\Helpers\HttpHeader;
use App\Models\User as ModelUser;
use App\Routes\Web;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Factories\UserFactory;

test('guests are redirected to login when visiting the notice', function (): void {
    $this->get(Web::verificationNotice->value)
        ->assertRedirect(Web::login->value);
});

test('an unverified user can view the notice', function (): void {
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $this->actingAs($ModelUser)
        ->get(Web::verificationNotice->value)
        ->assertOk();
});

test('a verified user visiting the notice is redirected home', function (): void {
    $ModelUser = ModelUser::factory()->createOne();

    $this->actingAs($ModelUser)
        ->get(Web::verificationNotice->value)
        ->assertRedirect(Web::home->value);
});

test('a verified user reaches a protected route in production', function (): void {
    config(['app.env' => 'production']);
    $ModelUser = ModelUser::factory()->createOne();

    $this->actingAs($ModelUser)
        ->get(Web::dashboard->value)
        ->assertNoContent();
});

test('an unverified user reaches a protected route outside production', function (): void {
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $this->actingAs($ModelUser)
        ->get(Web::dashboard->value)
        ->assertNoContent();
});

test('an unverified user is redirected to the notice from a protected route in production', function (): void {
    config(['app.env' => 'production']);
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $this->actingAs($ModelUser)
        ->get(Web::dashboard->value)
        ->assertRedirect(Web::verificationNotice->value);
});

test('an unverified htmx request to a protected route returns a no content response with an hx redirect header in production', function (): void {
    config(['app.env' => 'production']);
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $this->actingAs($ModelUser)
        ->withHeader(HttpHeader::HxRequest->value, 'true')
        ->get(Web::dashboard->value)
        ->assertNoContent(403)
        ->assertHeader(HttpHeader::HxRedirect->value, Web::verificationNotice->value);
});

test('a valid signed link marks the user as verified', function (): void {
    Event::fake([Verified::class]);
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $ModelUser->getKey(),
        'hash' => sha1($ModelUser->getEmailForVerification()),
    ]);

    $this->actingAs($ModelUser)
        ->get($url)
        ->assertRedirect(Web::home->value);

    expect($ModelUser->refresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

test('an invalid hash is rejected and leaves the user unverified', function (): void {
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $ModelUser->getKey(),
        'hash' => sha1('not-the-right-email'),
    ]);

    $this->actingAs($ModelUser)
        ->get($url)
        ->assertForbidden();

    expect($ModelUser->refresh()->hasVerifiedEmail())->toBeFalse();
});

test('an unsigned link is rejected', function (): void {
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $url = route('verification.verify', [
        'id' => $ModelUser->getKey(),
        'hash' => sha1($ModelUser->getEmailForVerification()),
    ]);

    $this->actingAs($ModelUser)
        ->get($url)
        ->assertForbidden();

    expect($ModelUser->refresh()->hasVerifiedEmail())->toBeFalse();
});

test('resending the notification dispatches a new verification email', function (): void {
    Notification::fake();
    $ModelUser = ModelUser::factory()->unverified()->createOne();

    $this->actingAs($ModelUser)
        ->post(Web::verificationSend->value)
        ->assertRedirect()
        ->assertSessionHas('status', 'Verification link sent!');

    Notification::assertSentTo($ModelUser, VerifyEmail::class);
});

test('registering sends an email verification notification', function (): void {
    Notification::fake();
    $RegisterForm = UserFactory::factory()->make();

    $this->post(Web::register->value, $RegisterForm->toArray())
        ->assertRedirect(Web::home->value);

    $ModelUser = ModelUser::query()->where(User::email, $RegisterForm->email)->firstOrFail();

    Notification::assertSentTo($ModelUser, VerifyEmail::class);
    expect($ModelUser->hasVerifiedEmail())->toBeFalse();
});
