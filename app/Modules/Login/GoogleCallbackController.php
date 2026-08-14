<?php

namespace App\Modules\Login;

use App\Helpers\SocialiteDriver;
use App\Models\OauthProvider;
use App\Models\User;
use App\Routes\Web;
use App\Sources\Db\App\OauthProviders;
use App\Sources\Db\App\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Socialite;

readonly class GoogleCallbackController
{
    public function __invoke(): RedirectResponse
    {
        $google_user = Socialite::driver(SocialiteDriver::google->value)->user();

        if (! $google_user instanceof AbstractUser) {
            return redirect(Web::login->value)->withErrors([
                LoginForm::email => 'Google did not provide a verified email address.',
            ]);
        }

        $GoogleUser = GoogleUser::from($google_user->getRaw());

        if (! $this->hasVerifiedEmail($GoogleUser)) {
            return redirect(Web::login->value)->withErrors([
                LoginForm::email => 'Google did not provide a verified email address.',
            ]);
        }

        $User = User::query()->getConnection()->transaction(function () use ($GoogleUser): User {
            $OauthProvider = OauthProvider::query()->firstOrNew([
                OauthProviders::sub->value => $GoogleUser->sub,
            ]);

            $User = $OauthProvider->exists ? $OauthProvider->user : User::query()->firstOrCreate(
                [Users::email->value => $GoogleUser->email],
                [
                    Users::name->value => $GoogleUser->name ?: Str::before($GoogleUser->email, '@'),
                    Users::password->value => Str::random(64),
                ],
            );

            if (! $User->hasVerifiedEmail()) {
                $User->markEmailAsVerified();
            }

            $User->oauthProviders()->updateOrCreate(
                [OauthProviders::sub->value => $GoogleUser->sub],
                $GoogleUser->toArray(),
            );

            return $User;
        });

        Auth::login($User);
        request()->session()->regenerate();

        return redirect()->intended(Web::home->value);
    }

    private function hasVerifiedEmail(GoogleUser $GoogleUser): bool
    {
        return filter_var($GoogleUser->email, FILTER_VALIDATE_EMAIL) !== false
            && $GoogleUser->email_verified
            && $GoogleUser->verified_email;
    }
}
