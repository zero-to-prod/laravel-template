<?php

namespace App\Modules\Login;

use App\Routes\Web;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class LoginController
{
    public function __invoke(): RedirectResponse
    {
        $LoginForm = LoginForm::from(request()->all());
        $Validator = Validator::make($LoginForm->toArray(), $LoginForm->rules());

        if (Auth::attempt($Validator->validate(), $LoginForm->remember_token)) {
            request()->session()->regenerate();

            return redirect()->intended(Web::home->value);
        }

        throw ValidationException::withMessages([
            LoginForm::email => [trans('auth.failed')],
        ]);
    }
}
