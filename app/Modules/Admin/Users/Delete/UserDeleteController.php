<?php

namespace App\Modules\Admin\Users\Delete;

use App\Models\User;
use App\Routes\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class UserDeleteController
{
    public const string confirmation = 'confirmation';

    public function __invoke(Request $Request, string $user): RedirectResponse
    {
        $User = User::query()->whereKey($user)->first();

        if ($User === null) {
            abort(404);
        }

        if ($User->is(User::authenticated($Request))) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        if ($Request->string(self::confirmation)->toString() !== 'delete') {
            return back()->withErrors(['delete' => 'Type delete to confirm.']);
        }

        $User->delete();

        return redirect(Admin::users->value)->with('status', 'User deleted.');
    }
}
