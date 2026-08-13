<?php

namespace App\Modules\Admin\Users\Update;

use App\Helpers\Role;
use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

readonly class UsersUpdateController
{
    public function __invoke(Request $Request, string $user): RedirectResponse
    {
        $User = User::query()->whereKey($user)->first();

        if ($User === null) {
            abort(404);
        }

        $UsersUpdateRequest = UsersUpdateRequest::from($Request->all());
        $Validator = Validator::make(...$UsersUpdateRequest->validator());
        $violations = $this->violations($Request, $User, $UsersUpdateRequest);

        if ($Validator->fails() || $violations !== []) {
            return back()
                ->withErrors($Validator->errors()->merge($violations))
                ->withInput($UsersUpdateRequest->toArray());
        }

        $User->name = $UsersUpdateRequest->name;
        $User->email = $UsersUpdateRequest->email;
        $User->email_verified_at = $UsersUpdateRequest->verified
            ? $User->email_verified_at ?? Carbon::now()
            : null;
        $User->save();

        $UsersUpdateRequest->admin
            ? $User->assignRole(Role::admin->value)
            : $User->removeRole(Role::admin->value);

        return back()->with('status', 'User updated.');
    }

    /** @return array<string, list<string>> */
    private function violations(Request $Request, User $User, UsersUpdateRequest $UsersUpdateRequest): array
    {
        $violations = [];

        $taken = User::query()
            ->where(Users::email->value, $UsersUpdateRequest->email)
            ->whereKeyNot($User->getKey())
            ->exists();

        if ($taken) {
            $violations[UsersUpdateRequest::email] = ['That email is already taken.'];
        }

        if (! $UsersUpdateRequest->admin && $User->is(User::authenticated($Request))) {
            $violations[UsersUpdateRequest::admin] = ['You cannot revoke your own admin role.'];
        }

        return $violations;
    }
}
