<?php

namespace Tests\Behavior\Web;

use App\Models\User;
use App\Modules\Register\RegisterForm;
use App\Modules\Register\RegisterFormFactory;
use App\Routes\Web;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
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
        $RegisterForm = RegisterFormFactory::factory()->make();

        $this->post(
            Web::register->value,
            $RegisterForm->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
        $this->assertDatabaseHas((new User)->getTable(), [
            User::name => $RegisterForm->name,
            User::email => $RegisterForm->email,
        ]);
    }

    #[Test]
    public function validation_fails_with_invalid_name(): void
    {
        $this->post(
            Web::register->value,
            RegisterFormFactory::factory()->set(RegisterForm::name, '')->context()
        )->assertSessionHasErrors(RegisterForm::name);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_invalid_email(): void
    {
        $this->post(
            Web::register->value,
            RegisterFormFactory::factory()->set(RegisterForm::email, '')->context()
        )->assertSessionHasErrors(RegisterForm::email);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_duplicate_email(): void
    {
        $RegisterForm = RegisterFormFactory::factory()->make();
        User::factory()->create([User::email => $RegisterForm->email]);

        $this->post(
            Web::register->value,
            $RegisterForm->toArray()
        )->assertSessionHasErrors(RegisterForm::email);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_mismatched_passwords(): void
    {
        $this->post(
            Web::register->value,
            RegisterFormFactory::factory()->set(RegisterForm::password_confirmation, 'mismatch')->context()
        )->assertSessionHasErrors(RegisterForm::password);

        $this->assertGuest();
    }

    #[Test]
    public function password_is_hashed_after_registration(): void
    {
        $RegisterForm = RegisterFormFactory::factory()->make();

        $this->post(Web::register->value, $RegisterForm->toArray());

        $user = User::where(User::email, $RegisterForm->email)->first();
        $this->assertNotEquals($RegisterForm->password, $user->password);
        $this->assertTrue(Hash::check($RegisterForm->password, $user->password));
    }

    #[Test]
    public function validation_fails_with_missing_required_fields(): void
    {
        $this->post(Web::register->value)
            ->assertSessionHasErrors([
                RegisterForm::name,
                RegisterForm::email,
                RegisterForm::password,
            ]);

        $this->assertGuest();
    }

    #[Test]
    public function old_input_is_preserved_on_validation_failure(): void
    {
        $RegisterForm = RegisterFormFactory::factory()
            ->set(RegisterForm::email, 'invalid-email')
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
            RegisterFormFactory::factory()->make()->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
    }
}
