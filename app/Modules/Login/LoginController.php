<?php

namespace App\Modules\Login;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class LoginController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(): RedirectResponse
    {
        $LoginForm = LoginForm::from(request()->all());
        $Validator = Validator::make($LoginForm->toArray(), $LoginForm->rules());

        if (Auth::attempt($Validator->validate(), $LoginForm->remember_token)) {
            request()->session()->regenerate();

            return redirect()->intended(web()->home);
        }

        throw ValidationException::withMessages([
            LoginForm::email => [trans('auth.failed')],
        ]);
    }
}