<?php

namespace App\Modules\Settings\Credentials;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class TokenDestroyController
{
    public function __invoke(Request $Request, string $credential): RedirectResponse
    {
        TokenQuery::find($Request, $credential)->delete();

        return back()->with('status', 'Token revoked.');
    }
}
