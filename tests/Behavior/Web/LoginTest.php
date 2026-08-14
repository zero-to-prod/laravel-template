<?php

use App\Helpers\OauthProviderId;
use App\Helpers\SessionKey;
use App\Helpers\SocialiteDriver;
use App\Models\OauthProvider;
use App\Models\User;
use App\Modules\Login\LoginForm;
use App\Modules\Login\LoginFormFactory;
use App\Routes\Web;
use App\Sources\Db\App\OauthProviders;
use App\Sources\Db\App\Users;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery\MockInterface;

test('route is accessible', function (): void {
    $this->get(Web::login->value)
        ->assertOk()
        ->assertSee(Web::googleRedirect->value)
        ->assertSee('Google');
});

test('google login redirects to google', function (): void {
    Socialite::fake(SocialiteDriver::google->value);

    $this->get(Web::googleRedirect->value)
        ->assertRedirect('https://socialite.fake/google/authorize');
});

test('google login creates a verified user', function (): void {
    Socialite::fake(SocialiteDriver::google->value, GoogleUser::fake([
        'sub' => '123456789',
        'name' => 'Google User',
        'given_name' => 'Google',
        'family_name' => 'User',
        'picture' => 'https://example.com/avatar.jpg',
        'email' => 'google@example.com',
        'email_verified' => true,
        'verified_email' => true,
    ]));

    $this->get(Web::googleCallback->value)->assertRedirect(Web::home->value);

    $User = User::query()->where(Users::email->value, 'google@example.com')->sole();

    $this->assertAuthenticatedAs($User);
    expect($User->name)->toBe('Google User')
        ->and($User->hasVerifiedEmail())->toBeTrue()
        ->and($User->oauthProviders()->sole()->sub)->toBe('123456789')
        ->and(session(SessionKey::user_picture->value))->toBe('https://example.com/avatar.jpg');
});

test('google login updates the oauth provider', function (): void {
    $User = User::factory()->createOne([
        Users::email->value => 'google@example.com',
    ]);
    $User->oauthProviders()->create([
        OauthProviders::provider_id->value => OauthProviderId::google->value,
        OauthProviders::sub->value => '123456789',
        OauthProviders::name->value => 'Old Name',
        OauthProviders::given_name->value => 'Old',
        OauthProviders::family_name->value => 'Name',
        OauthProviders::picture->value => 'https://example.com/old.jpg',
        OauthProviders::email->value => 'old@example.com',
        OauthProviders::email_verified->value => true,
        OauthProviders::id->value => '123456789',
        OauthProviders::verified_email->value => true,
    ]);
    Socialite::fake(SocialiteDriver::google->value, GoogleUser::fake([
        'sub' => '123456789',
        'name' => 'New Name',
        'given_name' => 'New',
        'family_name' => 'Name',
        'picture' => 'https://example.com/new.jpg',
        'email' => 'new@example.com',
        'email_verified' => true,
        'verified_email' => true,
    ]));

    $this->get(Web::googleCallback->value)->assertRedirect(Web::home->value);

    $OauthProvider = OauthProvider::query()->where(OauthProviders::sub->value, '123456789')->sole();

    $this->assertAuthenticatedAs($User);
    expect($OauthProvider->name)->toBe('New Name')
        ->and($OauthProvider->provider_id)->toBe(OauthProviderId::google)
        ->and($OauthProvider->email)->toBe('new@example.com')
        ->and($OauthProvider->picture)->toBe('https://example.com/new.jpg')
        ->and(OauthProvider::query()->where(OauthProviders::sub->value, '123456789')->count())->toBe(1);
});

test('google login uses an existing user', function (): void {
    $User = User::factory()->unverified()->createOne([
        Users::email->value => 'google@example.com',
    ]);
    Socialite::fake(SocialiteDriver::google->value, GoogleUser::fake([
        'sub' => '123456789',
        'given_name' => 'Test',
        'family_name' => 'User',
        'picture' => 'https://example.com/avatar.jpg',
        'email' => 'google@example.com',
        'email_verified' => true,
        'verified_email' => true,
    ]));

    $this->get(Web::googleCallback->value)->assertRedirect(Web::home->value);

    $this->assertAuthenticatedAs($User);
    expect($User->refresh()->hasVerifiedEmail())->toBeTrue()
        ->and(User::query()->where(Users::email->value, 'google@example.com')->count())->toBe(1);
});

test('google login rejects an unverified email', function (): void {
    Socialite::fake(SocialiteDriver::google->value, GoogleUser::fake([
        'sub' => '123456789',
        'given_name' => 'Test',
        'family_name' => 'User',
        'picture' => 'https://example.com/avatar.jpg',
        'email' => 'google@example.com',
        'email_verified' => false,
        'verified_email' => false,
    ]));

    $this->get(Web::googleCallback->value)
        ->assertRedirect(Web::login->value)
        ->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
    expect(User::query()->where(Users::email->value, 'google@example.com')->doesntExist())->toBeTrue();
});

test('google login rejects an unexpected socialite user', function (): void {
    /** @var SocialiteUser&MockInterface $SocialiteUser */
    $SocialiteUser = mock(SocialiteUser::class);
    Socialite::fake(SocialiteDriver::google->value, $SocialiteUser);

    $this->get(Web::googleCallback->value)
        ->assertRedirect(Web::login->value)
        ->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('google login redirects stale callbacks back to login', function (): void {
    $SocialiteProvider = new class implements SocialiteProvider
    {
        public function redirect(): never
        {
            throw new LogicException('Not used by this test.');
        }

        public function user(): never
        {
            throw new InvalidStateException;
        }
    };
    Socialite::shouldReceive('driver')
        ->once()
        ->with(SocialiteDriver::google->value)
        ->andReturn($SocialiteProvider);

    $this->get(Web::googleCallback->value)
        ->assertRedirect(Web::login->value)
        ->assertSessionHasErrors([
            LoginForm::email => 'Your Google sign-in session expired. Please try again.',
        ]);

    $this->assertGuest();
});

test('login with valid credentials', function (): void {
    $User = User::factory([Users::password->value => Users::password->value])->createOne();
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $User->email])
        ->set([LoginForm::password => Users::password->value])
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});

test('validation fails with invalid email', function (): void {
    $this->post(
        Web::login->value,
        LoginFormFactory::factory()->set([LoginForm::email => ''])->context()
    )->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('validation fails with invalid password', function (): void {
    $this->post(
        Web::login->value,
        LoginFormFactory::factory()->set([LoginForm::password => ''])->context()
    )->assertSessionHasErrors(LoginForm::password);

    $this->assertGuest();
});

test('login fails with invalid credentials', function (): void {
    $user = User::factory()->createOne();
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $user->email])
        ->set([LoginForm::password => 'wrong-password'])
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('login fails with non existent user', function (): void {
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => 'nonexistent@example.com'])
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertSessionHasErrors(LoginForm::email);

    $this->assertGuest();
});

test('user can login with remember me', function (): void {
    $User = User::factory()->createOne();
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $User->email])
        ->set([LoginForm::remember_token => true])
        ->make();

    $response = $this->post(
        Web::login->value,
        $LoginForm->toArray()
    );

    $response->assertRedirect(Web::home->value);
    $this->assertAuthenticatedAs($User);
    expect($User->refresh()->remember_token)->not->toBeNull();
});

test('user stays logged in with remember me', function (): void {
    $User = User::factory()->createOne();
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $User->email])
        ->set([LoginForm::remember_token => true])
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    );

    $this->post(Web::logout->value);
    $this->withSession([]);

    $this->get(Web::home->value);

    $this->assertAuthenticatedAs($User);
});

test('validation errors are displayed on the form', function (): void {
    $this->from(Web::login->value)
        ->followingRedirects()
        ->post(
            Web::login->value,
            LoginFormFactory::factory()->set([LoginForm::email => ''])->context()
        )
        ->assertOk()
        ->assertSee('The email field is required.');
});

test('old input is preserved on validation failure', function (): void {
    $LoginForm = LoginFormFactory::factory()->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )
        ->assertSessionHasInput(LoginForm::email)
        ->assertSessionMissing(LoginForm::password);

    $this->assertGuest();
});

test('intended url is preserved after login', function (): void {
    $user = User::factory()->createOne();
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $user->email])
        ->make();

    session(['url.intended' => Web::home->value]);

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});

test('input is sanitized during login', function (): void {
    User::factory()->createOne([
        Users::email->value => 'test@example.com',
    ]);

    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => ' TEST@EXAMPLE.COM '])
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);

    $this->assertAuthenticated();
});

test('validation fails with missing required fields', function (): void {
    $this->post(Web::login->value)
        ->assertSessionHasErrors([
            LoginForm::email,
            LoginForm::password,
        ]);

    $this->assertGuest();
});

test('user cannot login when already authenticated', function (): void {
    $user = User::factory()->createOne();
    $this->actingAs($user);

    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $user->email])
        ->make();

    $this->post(
        Web::login->value,
        $LoginForm->toArray()
    )->assertRedirect(Web::home->value);
});

test('failed authentication is displayed on the form', function (): void {
    $User = User::factory()->createOne();
    $LoginForm = LoginFormFactory::factory()
        ->set([LoginForm::email => $User->email])
        ->set([LoginForm::password => 'wrong-password'])
        ->make();

    $this->from(Web::login->value)
        ->followingRedirects()
        ->post(Web::login->value, $LoginForm->toArray())
        ->assertOk()
        ->assertSee('These credentials do not match our records.');
});
