<?php

namespace Tests\Behavior\Web;

use App\DataModels\User;
use \App\Models\User as ModelUser;
use App\Modules\Login\LoginForm;
use App\Modules\Login\LoginFormFactory;
use App\Routes\Web;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    #[Test]
    public function route_is_accessible(): void
    {
        $this->get(Web::login->value)->assertOk();
    }

    #[Test]
    public function login_with_valid_credentials(): void
    {
        $User = ModelUser::factory([User::password => User::password])->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $User->email)
            ->set(LoginForm::password, User::password)
            ->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
    }

    #[Test]
    public function validation_fails_with_invalid_email(): void
    {
        $this->post(
            Web::login->value,
            LoginFormFactory::factory()->set(LoginForm::email, '')->context()
        )->assertSessionHasErrors(LoginForm::email);

        $this->assertGuest();
    }

    #[Test]
    public function validation_fails_with_invalid_password(): void
    {
        $this->post(
            Web::login->value,
            LoginFormFactory::factory()->set(LoginForm::password, '')->context()
        )->assertSessionHasErrors(LoginForm::password);

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $user = ModelUser::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $user->email)
            ->set(LoginForm::password, 'wrong-password')
            ->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )->assertSessionHasErrors(LoginForm::email);

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_with_non_existent_user(): void
    {
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, 'nonexistent@example.com')
            ->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )->assertSessionHasErrors(LoginForm::email);

        $this->assertGuest();
    }

    #[Test]
    public function user_can_login_with_remember_me(): void
    {
        $User = ModelUser::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $User->email)
            ->set(LoginForm::remember_token, true)
            ->make();

        $response = $this->post(
            Web::login->value,
            $LoginForm->toArray()
        );

        $response->assertRedirect(Web::home->value);
        $this->assertAuthenticatedAs($User);
        $this->assertNotNull($User->fresh()->remember_token);
    }

    #[Test]
    public function user_stays_logged_in_with_remember_me(): void
    {
        $User = ModelUser::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $User->email)
            ->set(LoginForm::remember_token, true)
            ->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        );

        $this->post(Web::logout->value);
        $this->withSession([]);

        $this->get(Web::home->value);

        $this->assertAuthenticatedAs($User);
    }

    #[Test]
    public function old_input_is_preserved_on_validation_failure(): void
    {
        $LoginForm = LoginFormFactory::factory()->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )
            ->assertSessionHasInput(LoginForm::email)
            ->assertSessionMissing(LoginForm::password);

        $this->assertGuest();
    }

    #[Test]
    public function intended_url_is_preserved_after_login(): void
    {
        $user = ModelUser::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $user->email)
            ->make();

        session(['url.intended' => Web::home->value]);

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
    }

    #[Test]
    public function input_is_sanitized_during_login(): void
    {
        ModelUser::factory()->create([
            User::email => 'test@example.com',
        ]);

        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, ' TEST@EXAMPLE.COM ')
            ->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )->assertRedirect(Web::home->value);

        $this->assertAuthenticated();
    }

    #[Test]
    public function validation_fails_with_missing_required_fields(): void
    {
        $this->post(Web::login->value)
            ->assertSessionHasErrors([
                LoginForm::email,
                LoginForm::password,
            ]);

        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_login_when_already_authenticated(): void
    {
        $user = ModelUser::factory()->create();
        $this->actingAs($user);

        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $user->email)
            ->make();

        $this->post(
            Web::login->value,
            $LoginForm->toArray()
        )->assertRedirect(Web::home->value);
    }
}
