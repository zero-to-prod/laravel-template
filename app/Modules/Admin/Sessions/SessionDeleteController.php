<?php

namespace App\Modules\Admin\Sessions;

use App\Models\Session;
use App\Models\User;
use App\Routes\Admin;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class SessionDeleteController
{
    public function __invoke(Request $Request, string $session): RedirectResponse
    {
        $Session = Session::query()->find($session);

        if ($Session === null) {
            abort(404);
        }

        Session::query()->getConnection()->transaction(static function () use ($Session): void {
            $Session->delete();

            if (is_string($Session->user_id)) {
                User::query()->whereKey($Session->user_id)->update([Users::remember_token->value => null]);
            }
        });

        if ($Request->session()->getId() === $session) {
            Auth::logout();
            $Request->session()->invalidate();
            $Request->session()->regenerateToken();

            return redirect(Web::login->value)->with('status', 'Session revoked.');
        }

        return redirect(Admin::sessions->value)->with('status', 'Session revoked.');
    }
}
