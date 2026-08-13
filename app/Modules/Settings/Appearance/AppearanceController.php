<?php

namespace App\Modules\Settings\Appearance;

use App\Helpers\Theme;
use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

readonly class AppearanceController
{
    public function __invoke(Request $Request): RedirectResponse
    {
        $AppearanceRequest = AppearanceRequest::from($Request->all());
        $Validator = Validator::make(...$AppearanceRequest->validator());

        if ($Validator->fails()) {
            return back()->withErrors($Validator);
        }

        User::authenticated($Request)
            ->update([Users::theme->value => Theme::from($AppearanceRequest->theme)]);

        return back()->with('status', 'Appearance updated.');
    }
}
