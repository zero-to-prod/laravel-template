<?php

namespace App\Modules\PasswordReset;

use App\Routes\Web;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

readonly class ForgotPasswordController
{
    public function __invoke(): RedirectResponse
    {
        $ForgotPasswordRequest = ForgotPasswordRequest::from(request()->all());
        $Validator = Validator::make(...$ForgotPasswordRequest->validator());

        if ($Validator->fails()) {
            return back()
                ->withErrors($Validator)
                ->withInput($ForgotPasswordRequest->toArray());
        }

        Password::sendResetLink($Validator->validated());

        return redirect(Web::forgotPasswordSent->value);
    }
}
