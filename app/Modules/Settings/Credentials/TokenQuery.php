<?php

namespace App\Modules\Settings\Credentials;

use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Sources\Db\App\PersonalAccessTokens;
use Illuminate\Http\Request;

class TokenQuery
{
    /**
     * One token of the authenticated account, or nothing at all.
     *
     * A token is addressed by an id a caller can guess, so the lookup is scoped to the
     * owner rather than filtered afterwards: an unscoped read would let one account
     * name another's token and be answered.
     */
    public static function find(Request $Request, string $credential): PersonalAccessToken
    {
        $Token = User::authenticated($Request)->tokens()->whereKey($credential)->first();

        if (! $Token instanceof PersonalAccessToken) {
            abort(404);
        }

        return $Token;
    }

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
