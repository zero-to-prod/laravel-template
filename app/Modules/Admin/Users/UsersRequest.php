<?php

namespace App\Modules\Admin\Users;

use App\Helpers\DataModel;
use App\Helpers\DataModelCast;
use App\Helpers\SortDirection;
use App\Models\User;
use App\Sources\Db\App\Users;
use App\View\DataModels\UsersTable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Zerotoprod\DataModel\Describe;

readonly class UsersRequest
{
    use DataModel;

    public const string search = 'search';

    #[Describe([Describe::required => true])]
    public string $search;

    public const string sort = 'sort';

    #[Describe([Describe::required => true])]
    public Users $sort;

    public const string direction = 'direction';

    #[Describe([Describe::required => true])]
    public SortDirection $direction;

    public static function of(Request $Request): self
    {
        return self::from([
            self::search => DataModelCast::sanitize(self::string($Request, self::search)),
            self::sort => self::toSort(self::string($Request, self::sort)),
            self::direction => self::toDirection(self::string($Request, self::direction)),
        ]);
    }

    public function searching(): bool
    {
        return $this->search !== '';
    }

    /**
     * @param  LengthAwarePaginator<int, User>  $LengthAwarePaginator
     * @return array<string, mixed>
     */
    public function table(LengthAwarePaginator $LengthAwarePaginator): array
    {
        return [
            UsersTable::search => $this->search,
            UsersTable::sort => $this->sort,
            UsersTable::direction => $this->direction,
            UsersTable::paginator => $LengthAwarePaginator,
        ];
    }

    private static function toSort(?string $value): Users
    {
        $Column = $value === null ? null : Users::tryFrom($value);

        return $Column !== null && in_array($Column, UsersTable::columns(), true)
            ? $Column
            : UsersTable::columns()[0];
    }

    private static function toDirection(?string $value): SortDirection
    {
        return ($value === null ? null : SortDirection::tryFrom($value)) ?? SortDirection::asc;
    }

    private static function string(Request $Request, string $key): ?string
    {
        $value = $Request->query($key);

        return is_string($value) ? $value : null;
    }
}
