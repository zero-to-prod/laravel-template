<?php

namespace App\Modules\Settings\Credentials;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class TokenDestroyController
{
    public function __invoke(Request $Request, string $credential): RedirectResponse
    {
        $Token = User::authenticated($Request)->tokens()->whereKey($credential)->first();

        if ($Token === null) {
            abort(404);
        }

        $Token->delete();

        return back()->with('status', 'Token revoked.');
    }
}
