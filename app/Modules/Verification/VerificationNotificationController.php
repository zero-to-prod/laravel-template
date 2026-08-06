<?php

namespace App\Modules\Verification;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class VerificationNotificationController
{
    /** @throws AuthenticationException */
    public function __invoke(Request $Request): RedirectResponse
    {
        User::authenticated($Request)->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    }
}
