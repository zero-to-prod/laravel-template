<?php

namespace App\Modules\Admin\Sessions;

use App\Models\Session;
use App\Sources\Db\App\Sessions;
use App\Sources\Db\App\Users;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

readonly class SessionsQuery
{
    public const int perPage = 15;

    /** @return LengthAwarePaginator<int, object> */
    public static function get(?string $userId = null, string $email = ''): LengthAwarePaginator
    {
        $term = '%'.addcslashes(trim($email), '%_\\').'%';

        return Session::query()->toBase()
            ->leftJoin(Users::table(), Sessions::table().'.'.Sessions::user_id->value, '=', Users::table().'.'.Users::id->value)
            ->when($userId !== null, static fn ($Query) => $Query->where(Sessions::table().'.'.Sessions::user_id->value, $userId))
            ->when($email !== '', static fn ($Query) => $Query->where(Users::table().'.'.Users::email->value, 'like', $term))
            ->select([
                Sessions::table().'.'.Sessions::id->value,
                Sessions::table().'.'.Sessions::user_id->value,
                Sessions::table().'.'.Sessions::ip_address->value,
                Sessions::table().'.'.Sessions::user_agent->value,
                Sessions::table().'.'.Sessions::last_activity->value,
                Users::table().'.'.Users::email->value,
            ])
            ->orderByDesc(Sessions::table().'.'.Sessions::last_activity->value)
            ->paginate(self::perPage);
    }

    public static function lastActivity(string $userId): ?Carbon
    {
        $timestamp = Session::query()
            ->where(Sessions::user_id->value, $userId)
            ->max(Sessions::last_activity->value);

        return is_numeric($timestamp) ? Carbon::createFromTimestamp((int) $timestamp) : null;
    }
}
