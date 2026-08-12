<?php

namespace App\Modules\Settings\Authentication;

use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionException;

readonly class PasswordController
{
    /**
     * @throws AuthenticationException
     * @throws ReflectionException
     */
    public function __invoke(Request $Request): RedirectResponse
    {
        $PasswordRequest = PasswordRequest::from($Request->all());
        $Validator = Validator::make(...$PasswordRequest->validator());

        if ($Validator->fails()) {
            return back()->withErrors($Validator);
        }

        User::authenticated($Request)
            ->update([Users::password->value => $PasswordRequest->password]);

        return back()->with('status', 'Password updated.');
    }
}
