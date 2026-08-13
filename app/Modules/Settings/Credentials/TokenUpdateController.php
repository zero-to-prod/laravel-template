<?php

namespace App\Modules\Settings\Credentials;

use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

readonly class TokenUpdateController
{
    public function __invoke(Request $Request, string $credential): RedirectResponse
    {
        TokenQuery::find($Request, $credential)->forceFill([
            PersonalAccessTokens::abilities->value => TokenUpdateRequest::from($Request->all())->abilities,
        ])->save();

        return back()->with('status', 'Abilities updated.');
    }
}
