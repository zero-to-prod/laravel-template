<?php

namespace App\Modules\Settings\Sessions;

use App\Models\Session;
use App\Models\User;
use App\Routes\Auth as AuthRoute;
use App\Routes\Web;
use App\Sources\Db\App\Sessions;
use App\Sources\Db\App\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class SessionDestroyController
{
    public function __invoke(Request $Request, string $session): RedirectResponse
    {
        $User = User::authenticated($Request);
        $Session = Session::query()
            ->where(Sessions::id->value, $session)
            ->where(Sessions::user_id->value, $User->id)
            ->first();

        if ($Session === null) {
            abort(404);
        }

        Session::query()->getConnection()->transaction(static function () use ($Session, $User): void {
            $Session->delete();
            $User->forceFill([Users::remember_token->value => null])->save();
        });

        if ($Request->session()->getId() === $session) {
            Auth::logout();
            $Request->session()->invalidate();
            $Request->session()->regenerateToken();

            return redirect(Web::login->value)->with('status', 'Session revoked.');
        }

        return redirect(AuthRoute::settingsSessions->value)->with('status', 'Session revoked.');
    }
}
