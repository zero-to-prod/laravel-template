<?php

namespace App\Modules\Verification;

use App\Routes\Web;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

readonly class VerifyEmailController
{
    public function __invoke(EmailVerificationRequest $EmailVerificationRequest): RedirectResponse
    {
        $EmailVerificationRequest->fulfill();

        return redirect(Web::home->value);
    }
}
