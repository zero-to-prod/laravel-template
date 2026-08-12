<?php

namespace App\Modules\Settings\Profile;

use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionException;

readonly class ProfileController
{
    /**
     * @throws AuthenticationException
     * @throws ReflectionException
     */
    public function __invoke(Request $Request): RedirectResponse
    {
        $ProfileRequest = ProfileRequest::from($Request->all());
        $Validator = Validator::make(...$ProfileRequest->validator());

        if ($Validator->fails()) {
            return back()
                ->withErrors($Validator)
                ->withInput($ProfileRequest->toArray());
        }

        User::authenticated($Request)
            ->update([Users::name->value => $ProfileRequest->name]);

        return back()->with('status', 'Profile updated.');
    }
}
