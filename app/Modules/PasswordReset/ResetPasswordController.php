<?php

namespace App\Modules\PasswordReset;

use App\Models\User;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

readonly class ResetPasswordController
{
    public function __invoke(string $token): RedirectResponse
    {
        $ResetPasswordRequest = ResetPasswordRequest::from([
            ...request()->all(),
            ResetPasswordRequest::token => $token,
        ]);
        $Validator = Validator::make(...$ResetPasswordRequest->validator());

        if ($Validator->fails()) {
            return back()
                ->withErrors($Validator)
                ->withInput(request()->only(ResetPasswordRequest::email));
        }

        $status = Password::reset(
            $Validator->validated(),
            static function (User $User, string $password): void {
                $User->forceFill([
                    Users::password->value => $password,
                    Users::remember_token->value => Str::random(60),
                ])->save();

                event(new PasswordReset($User));
            },
        );

        if ($status === Password::PasswordReset) {
            return redirect(Web::login->value)->with('status', trans($status));
        }

        return back()
            ->withErrors([
                ResetPasswordRequest::email => trans(is_string($status) ? $status : 'passwords.token'),
            ])
            ->withInput(request()->only(ResetPasswordRequest::email));
    }
}
