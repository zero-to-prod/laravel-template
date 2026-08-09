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
        $LoginRequest = LoginRequest::from(request()->all());
        $Validator = Validator::make($LoginRequest->toArray(), $LoginRequest->rules());

        if (Auth::attempt($Validator->validate(), $LoginRequest->remember_token)) {
            request()->session()->regenerate();

            return redirect()->intended(Web::home->value);
        }

        throw ValidationException::withMessages([
            LoginForm::email => [trans('auth.failed')],
        ]);
    }
}
