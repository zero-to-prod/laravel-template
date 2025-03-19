<?php

namespace Tests\Behavior;

use App\Models\User;
use App\Modules\Login\LoginForm;
use App\Modules\Login\LoginFormFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test] public function route_is_accessible(): void
    {
        $this->get(r()->login())->assertOk();
    }

    #[Test] public function login_with_valid_credentials(): void
    {
        $User = User::factory([User::password => User::password])->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $User->email)
            ->set(LoginForm::password, User::password)
            ->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )->assertRedirect(r()->home());

        $this->assertAuthenticated();
    }

    #[Test] public function validation_fails_with_invalid_email(): void
    {
        $this->post(
            r()->login(),
            LoginFormFactory::factory()->set(LoginForm::email, '')->context()
        )->assertSessionHasErrors(LoginForm::email);

        $this->assertGuest();
    }

    #[Test] public function validation_fails_with_invalid_password(): void
    {
        $this->post(
            r()->login(),
            LoginFormFactory::factory()->set(LoginForm::password, '')->context()
        )->assertSessionHasErrors(LoginForm::password);

        $this->assertGuest();
    }

    #[Test] public function login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $user->email)
            ->set(LoginForm::password, 'wrong-password')
            ->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )->assertSessionHasErrors(LoginForm::email);

        $this->assertGuest();
    }

    #[Test] public function login_fails_with_non_existent_user(): void
    {
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, 'nonexistent@example.com')
            ->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )->assertSessionHasErrors(LoginForm::email);

        $this->assertGuest();
    }

    #[Test] public function user_can_login_with_remember_me(): void
    {
        $User = User::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $User->email)
            ->set(LoginForm::remember_token, true)
            ->make();

        $response = $this->post(
            r()->login(),
            $LoginForm->toArray()
        );

        $response->assertRedirect(r()->home());
        $this->assertAuthenticatedAs($User);
        $this->assertNotNull($User->fresh()->remember_token);
    }

    #[Test] public function user_stays_logged_in_with_remember_me(): void
    {
        $User = User::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $User->email)
            ->set(LoginForm::remember_token, true)
            ->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        );

        $this->post(r()->logout());
        $this->withSession([]);

        $this->get(r()->home());

        $this->assertAuthenticatedAs($User);
    }

    #[Test] public function old_input_is_preserved_on_validation_failure(): void
    {
        $LoginForm = LoginFormFactory::factory()->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )
            ->assertSessionHasInput(LoginForm::email)
            ->assertSessionMissing(LoginForm::password);

        $this->assertGuest();
    }

    #[Test] public function intended_url_is_preserved_after_login(): void
    {
        $user = User::factory()->create();
        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $user->email)
            ->make();

        session(['url.intended' => r()->home()]);

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )->assertRedirect(r()->home());

        $this->assertAuthenticated();
    }

    #[Test] public function input_is_sanitized_during_login(): void
    {
        User::factory()->create([
            User::email => 'test@example.com'
        ]);

        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, ' TEST@EXAMPLE.COM ')
            ->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )->assertRedirect(r()->home());

        $this->assertAuthenticated();
    }

    #[Test] public function validation_fails_with_missing_required_fields(): void
    {
        $this->post(r()->login())
            ->assertSessionHasErrors([
                LoginForm::email,
                LoginForm::password,
            ]);

        $this->assertGuest();
    }

    #[Test] public function user_cannot_login_when_already_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $LoginForm = LoginFormFactory::factory()
            ->set(LoginForm::email, $user->email)
            ->make();

        $this->post(
            r()->login(),
            $LoginForm->toArray()
        )->assertRedirect(r()->home());
    }
}