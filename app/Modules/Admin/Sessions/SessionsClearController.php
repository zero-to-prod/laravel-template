<?php

namespace App\Modules\Admin\Sessions;

use App\Models\Session;
use App\Models\User;
use App\Routes\Admin;
use App\Routes\Web;
use App\Sources\Db\App\Sessions;
use App\Sources\Db\App\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

readonly class SessionsClearController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        $User = User::query()->findOrFail($Request->string(Admin::userParameter)->toString());

        Session::query()->getConnection()->transaction(static function () use ($User): void {
            Session::query()->where(Sessions::user_id->value, $User->id)->delete();
            $User->forceFill([Users::remember_token->value => null])->save();
        });

        if (Auth::id() === $User->id) {
            Auth::logout();
            $Request->session()->invalidate();
            $Request->session()->regenerateToken();

            return redirect(Web::login->value)->with('status', 'All user sessions cleared.');
        }

        return redirect(Admin::sessions->value.'?'.http_build_query([Admin::userParameter => $User->id]))
            ->with('status', 'All user sessions cleared.');
    }
}
