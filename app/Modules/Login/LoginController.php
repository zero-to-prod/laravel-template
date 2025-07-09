<?php

namespace App\Modules\Login;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Request $Request, LoginForm $LoginForm): RedirectResponse
    {
        if (Auth::attempt($LoginForm->validator()->validate(), $LoginForm->remember_token)) {
            $Request->session()->regenerate();

            return redirect()->intended(web()->home);
        }

        throw ValidationException::withMessages([
            LoginForm::email => [trans('auth.failed')],
        ]);
    }
}