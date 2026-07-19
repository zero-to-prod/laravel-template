<?php

namespace App\Modules\Verification;

use App\Routes\Web;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

readonly class VerifyEmailController
{
    public function __invoke(EmailVerificationRequest $Request): RedirectResponse
    {
        $Request->fulfill();

        return redirect(Web::home->value);
    }
}
