<?php

namespace Tests\Behavior\Web;

use App\DataModels\User;
use App\Models\Mailbox;
use App\Routes\Web;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\Factories\UserFactory;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    #[Test]
    public function route_is_accessible(): void
    {
        $this->get(Web::register->value)->assertOk();
    }

    #[Test]
    public function registration(): void
    {
        $RegisterForm = UserFactory::factory()->make();

        $this->post(
            Web::register->value,
            $RegisterForm->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
        $this->assertDatabaseHas((new \App\Models\User)->getTable(), [
            User::name => $RegisterForm->name,
            User::email => $RegisterForm->email,
        ]);
    }

    #[Test]
    public function validation_fails_with_invalid_name(): void
    {
        $this->post(
            Web::register->value,
            UserFactory::factory()->set(User::name, '')->context()
        )->assertSessionHasErrors(User::name);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_invalid_email(): void
    {
        $this->post(
            Web::register->value,
            UserFactory::factory()->set(User::email, '')->context()
        )->assertSessionHasErrors(User::email);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_duplicate_email(): void
    {
        $RegisterForm = UserFactory::factory()->make();
        \App\Models\User::factory()->create([User::email => $RegisterForm->email]);

        $this->post(
            Web::register->value,
            $RegisterForm->toArray()
        )->assertSessionHasErrors(User::email);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_mismatched_passwords(): void
    {
        $this->post(
            Web::register->value,
            UserFactory::factory()->set(User::password_confirmation, 'mismatch')->context()
        )->assertSessionHasErrors(User::password);

        $this->assertGuest();
    }

    #[Test]
    public function password_is_hashed_after_registration(): void
    {
        $RegisterForm = UserFactory::factory()->make();

        $this->post(Web::register->value, $RegisterForm->toArray());

        $user = \App\Models\User::where(User::email, $RegisterForm->email)->first();
        $this->assertNotEquals($RegisterForm->password, $user->password);
        $this->assertTrue(Hash::check($RegisterForm->password, $user->password));
    }

    #[Test]
    public function validation_fails_with_missing_required_fields(): void
    {
        $this->post(Web::register->value)
            ->assertSessionHasErrors([
                User::name,
                User::email,
                User::password,
            ]);

        $this->assertGuest();
    }

    #[Test]
    public function validation_errors_are_displayed_on_the_form(): void
    {
        $this->from(Web::register->value)
            ->followingRedirects()
            ->post(
                Web::register->value,
                UserFactory::factory()->set(User::name, '')->context()
            )
            ->assertOk()
            ->assertSee('The name field is required.');
    }

    #[Test]
    public function old_input_is_preserved_on_validation_failure(): void
    {
        $RegisterForm = UserFactory::factory()
            ->set(User::email, 'invalid-email')
            ->make();

        $this->post(Web::register->value, $RegisterForm->toArray())
            ->assertSessionHasInput($RegisterForm->name)
            ->assertSessionMissing($RegisterForm->password);

        $this->assertGuest();
    }

    public function test_intended_url_is_preserved_after_registration(): void
    {
        session(['url.intended' => Web::home->value]);

        $this->post(
            Web::register->value,
            UserFactory::factory()->make()->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
    }
}
