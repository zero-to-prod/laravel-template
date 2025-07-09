<?php

namespace App\Modules\Logout;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LogoutController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        Auth::logout();

        $Request->session()->invalidate();
        $Request->session()->regenerateToken();

        return redirect(web()->home);
    }
}