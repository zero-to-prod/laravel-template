<?php

namespace App\Modules\Verification;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class VerificationNotificationController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        User::authenticated($Request)->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    }
}
