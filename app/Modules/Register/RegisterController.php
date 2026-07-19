<?php

namespace App\Modules\Register;

use App\DataModels\Fields\GenericString;
use App\DataModels\User;
use App\Helpers\Rule;
use App\Routes\Web;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

readonly class RegisterController
{
    public function __invoke(RegisterConfig $RegisterConfig): RedirectResponse
    {
        $User = User::from(request()->all());
        $key = $RegisterConfig->rateLimitKey($User->email ?? '');

        $tooManyAttempts = RateLimiter::tooManyAttempts(
            $key,
            $RegisterConfig->rateLimitMaxAttempts()
        );

        if ($tooManyAttempts) {
            return back()->withErrors([
                User::email => $RegisterConfig->tooManyAttemptsMessage(),
            ]);
        }

        RateLimiter::hit($key);

        $rules = $User->rules();
        $rules[User::name] = [Rule::required->value, Rule::string->value, Rule::max(GenericString::length)];
        $rules[User::email] = [...$rules[User::email], Rule::unique('users')];
        $rules[User::password] = [Rule::required->value, Rule::confirmed->value, Password::defaults()];

        $Validator = Validator::make($User->toArray(), $rules);

        if ($Validator->fails()) {
            return back()
                ->withErrors($Validator)
                ->withInput($User->toArray());
        }

        DB::transaction(static function () use ($User) {
            $ModelUser = \App\Models\User::create([
                User::name => $User->name,
                User::email => $User->email,
                User::password => Hash::make($User->password),
            ]);

            Auth::login($ModelUser);

            event(new Registered($ModelUser));
        });

        return redirect()->intended(Web::home->value);
    }
}
