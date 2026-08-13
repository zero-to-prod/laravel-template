<?php

namespace App\Modules\Settings\Credentials;

use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Http\Request;

class TokenQuery
{
    /** @return list<array<string, mixed>> */
    public static function get(Request $Request): array
    {
        $Tokens = User::authenticated($Request)
            ->tokens()
            ->latest(PersonalAccessTokens::created_at->value)
            ->latest(PersonalAccessTokens::id->value)
            ->get();

        return array_values(array_map(
            /** @return array<string, mixed> */
            static fn (PersonalAccessToken $Token): array => $Token->toArray(),
            $Tokens->all(),
        ));
    }
}
