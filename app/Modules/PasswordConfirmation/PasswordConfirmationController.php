<?php

namespace App\Modules\PasswordConfirmation;

use App\Routes\Web;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

readonly class PasswordConfirmationController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        $PasswordConfirmationRequest = PasswordConfirmationRequest::from($Request->all());
        $Validator = Validator::make(...$PasswordConfirmationRequest->validator());

        if ($Validator->fails()) {
            return back()->withErrors($Validator);
        }

        $Request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(Web::home->value);
    }
}
