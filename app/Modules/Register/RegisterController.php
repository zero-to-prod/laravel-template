<?php

namespace App\Modules\Register;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController
{
    public function __invoke(RegisterForm $Form, RegisterConfig $Conf): RedirectResponse
    {
        $key = $Conf->rateLimitKey($Form->email ?? '');

        $tooManyAttempts = RateLimiter::tooManyAttempts(
            $key,
            $Conf->rateLimitMaxAttempts()
        );

        if ($tooManyAttempts) {
            return back()->withErrors([
                RegisterForm::email => $Conf->tooManyAttemptsMessage()
            ]);
        }

        RateLimiter::hit($key);

        $Validator = $Form->validator();

        if ($Validator->fails()) {
            return back()
                ->withErrors($Validator)
                ->withInput($Form->toArray());
        }

        Auth::login(
            User::create([
                User::name => $Form->name,
                User::email => $Form->email,
                User::password => Hash::make($Form->password),
            ])
        );

        return redirect()->intended(r()->home());
    }
}