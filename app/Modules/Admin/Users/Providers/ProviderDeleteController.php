<?php

namespace App\Modules\Admin\Users\Providers;

use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

readonly class ProviderDeleteController
{
    public function __invoke(string $user, string $provider): RedirectResponse
    {
        $User = User::query()->whereKey($user)->first();

        if ($User === null) {
            abort(404);
        }

        $OauthProvider = $User->oauthProviders()->whereKey($provider)->first();

        if (! $OauthProvider instanceof OauthProvider) {
            abort(404);
        }

        $OauthProvider->delete();

        return back()->with('status', 'Sign-in provider removed.');
    }
}
