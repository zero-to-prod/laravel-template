<?php

namespace App\Modules\Settings\Credentials;

use App\Models\User;
use App\View\DataModels\CredentialsTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

readonly class TokenController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        $TokenRequest = TokenRequest::from($Request->all());
        $Validator = Validator::make(...$TokenRequest->validator());

        if ($Validator->fails()) {
            return back()
                ->withErrors($Validator)
                ->withInput($TokenRequest->toArray());
        }

        $NewAccessToken = User::authenticated($Request)
            ->createToken($TokenRequest->name, expiresAt: $TokenRequest->expiresAt());

        return back()
            ->with(CredentialsTable::sessionKey, $NewAccessToken->plainTextToken)
            ->with('status', 'Token created.');
    }
}
