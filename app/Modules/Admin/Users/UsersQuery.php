<?php

namespace App\Modules\Admin\Users;

use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UsersQuery
{
    public const int perPage = 15;

    /** @return LengthAwarePaginator<int, User> */
    public static function get(UsersRequest $UsersRequest): LengthAwarePaginator
    {
        $Builder = User::query();

        if ($UsersRequest->searching()) {
            $term = '%'.addcslashes($UsersRequest->search, '%_\\').'%';

            $Builder->where(static function (Builder $Builder) use ($term): void {
                $Builder->where(Users::name->value, 'like', $term)
                    ->orWhere(Users::email->value, 'like', $term);
            });
        }

        return $Builder
            ->orderBy($UsersRequest->sort->value, $UsersRequest->direction->value)
            ->orderBy(Users::id->value)
            ->paginate(self::perPage)
            ->withQueryString();
    }
}
