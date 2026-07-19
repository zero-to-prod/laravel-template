<?php

namespace App\Modules\Verification;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class VerificationNotificationController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        $Request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    }
}
