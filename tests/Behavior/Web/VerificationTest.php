<?php

namespace Tests\Behavior\Web;

use App\Helpers\HttpHeader;
use App\Models\User as ModelUser;
use App\Routes\Web;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\Factories\UserFactory;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    #[Test]
    public function guests_are_redirected_to_login_when_visiting_the_notice(): void
    {
        $this->get(Web::verificationNotice->value)
            ->assertRedirect(Web::login->value);
    }

    #[Test]
    public function an_unverified_user_can_view_the_notice(): void
    {
        $ModelUser = ModelUser::factory()->unverified()->create();

        $this->actingAs($ModelUser)
            ->get(Web::verificationNotice->value)
            ->assertOk();
    }

    #[Test]
    public function a_verified_user_visiting_the_notice_is_redirected_home(): void
    {
        $ModelUser = ModelUser::factory()->create();

        $this->actingAs($ModelUser)
            ->get(Web::verificationNotice->value)
            ->assertRedirect(Web::home->value);
    }

    #[Test]
    public function an_unverified_user_is_redirected_to_the_notice_from_a_protected_route_in_production(): void
    {
        config(['app.env' => 'production']);
        $ModelUser = ModelUser::factory()->unverified()->create();

        $this->actingAs($ModelUser)
            ->get(Web::dashboard->value)
            ->assertRedirect(Web::verificationNotice->value);
    }

    #[Test]
    public function an_unverified_htmx_request_to_a_protected_route_returns_a_no_content_response_with_an_hx_redirect_header_in_production(): void
    {
        config(['app.env' => 'production']);
        $ModelUser = ModelUser::factory()->unverified()->create();

        $this->actingAs($ModelUser)
            ->withHeader(HttpHeader::HxRequest->value, 'true')
            ->get(Web::dashboard->value)
            ->assertNoContent(403)
            ->assertHeader(HttpHeader::HxRedirect->value, Web::verificationNotice->value);
    }

    #[Test]
    public function a_valid_signed_link_marks_the_user_as_verified(): void
    {
        Event::fake([Verified::class]);
        $ModelUser = ModelUser::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $ModelUser->getKey(),
            'hash' => sha1($ModelUser->getEmailForVerification()),
        ]);

        $this->actingAs($ModelUser)
            ->get($url)
            ->assertRedirect(Web::home->value);

        $this->assertTrue($ModelUser->refresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }

    #[Test]
    public function an_invalid_hash_is_rejected_and_leaves_the_user_unverified(): void
    {
        $ModelUser = ModelUser::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $ModelUser->getKey(),
            'hash' => sha1('not-the-right-email'),
        ]);

        $this->actingAs($ModelUser)
            ->get($url)
            ->assertForbidden();

        $this->assertFalse($ModelUser->refresh()->hasVerifiedEmail());
    }

    #[Test]
    public function an_unsigned_link_is_rejected(): void
    {
        $ModelUser = ModelUser::factory()->unverified()->create();

        $url = route('verification.verify', [
            'id' => $ModelUser->getKey(),
            'hash' => sha1($ModelUser->getEmailForVerification()),
        ]);

        $this->actingAs($ModelUser)
            ->get($url)
            ->assertForbidden();

        $this->assertFalse($ModelUser->refresh()->hasVerifiedEmail());
    }

    #[Test]
    public function resending_the_notification_dispatches_a_new_verification_email(): void
    {
        Notification::fake();
        $ModelUser = ModelUser::factory()->unverified()->create();

        $this->actingAs($ModelUser)
            ->post(Web::verificationSend->value)
            ->assertRedirect()
            ->assertSessionHas('status', 'Verification link sent!');

        Notification::assertSentTo($ModelUser, VerifyEmail::class);
    }

    #[Test]
    public function registering_sends_an_email_verification_notification(): void
    {
        Notification::fake();
        $RegisterForm = UserFactory::factory()->make();

        $this->post(Web::register->value, $RegisterForm->toArray())
            ->assertRedirect(Web::home->value);

        $ModelUser = ModelUser::where(ModelUser::email, $RegisterForm->email)->firstOrFail();

        Notification::assertSentTo($ModelUser, VerifyEmail::class);
        $this->assertFalse($ModelUser->hasVerifiedEmail());
    }
}
