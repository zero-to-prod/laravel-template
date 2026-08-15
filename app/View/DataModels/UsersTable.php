<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\SortDirection;
use App\Helpers\SvgName;
use App\Models\User;
use App\Modules\Admin\Users\UsersRequest;
use App\Routes\Admin;
use App\Sources\Db\App\Users;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Zerotoprod\DataModel\Describe;

readonly class UsersTable
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

    public const string paginator = 'paginator';

    /** @var LengthAwarePaginator<int, User> */
    #[Describe([Describe::required => true])]
    public LengthAwarePaginator $paginator;

    /**
     * The columns a page of users is listed by and ordered by.
     *
     * One list answers both questions, so a heading is never drawn for something the
     * ordering will not accept, and an ordering can never name a column the listing
     * does not show. The leading entry is what an absent or unrecognised ordering
     * falls back to. A column added here is read off the row by name, so one with no
     * property to read renders empty rather than failing.
     *
     * @return list<Users>
     */
    public static function columns(): array
    {
        return [Users::name, Users::email, Users::email_verified_at, Users::created_at];
    }

    /** @return list<SortableHeader> */
    public function headers(): array
    {
        return array_map(fn (Users $Column): SortableHeader => SortableHeader::from([
            SortableHeader::label => Str::headline($Column->name),
            SortableHeader::title => $Column->comment(),
            SortableHeader::url => $this->url($Column),
            SortableHeader::sorted => $Column === $this->sort,
            SortableHeader::direction => $Column === $this->sort ? $this->direction : SortDirection::asc,
        ]), self::columns());
    }

    /** @return list<UserRow> */
    public function rows(): array
    {
        return array_values($this->paginator->getCollection()
            ->map(static fn (User $User): UserRow => UserRow::from([
                ...$User->toArray(),
                UserRow::picture => $User->oauthProviders->first()?->picture,
            ]))
            ->all());
    }

    public function span(): int
    {
        return count(self::columns()) + 2;
    }

    public function searching(): bool
    {
        return $this->search !== '';
    }

    public function action(): string
    {
        return Admin::users->url();
    }

    /** @return array<string, string> */
    public function hidden(): array
    {
        return [
            UsersRequest::sort => $this->sort->value,
            UsersRequest::direction => $this->direction->value,
        ];
    }

    /** @return array<string, mixed> */
    public function searchInput(): array
    {
        return [
            TextInput::name => UsersRequest::search,
            TextInput::legend => 'Search',
            TextInput::icon => SvgName::magnifying_glass,
            TextInput::placeholder => 'Name or email',
            TextInput::title => 'Filter users by name or email',
            TextInput::value => $this->search,
        ];
    }

    public function summary(): string
    {
        $total = $this->paginator->total();

        return $total === 0
            ? 'No users'
            : "Showing {$this->paginator->firstItem()}–{$this->paginator->lastItem()} of $total";
    }

    public function previousUrl(): ?string
    {
        return $this->paginator->previousPageUrl();
    }

    public function nextUrl(): ?string
    {
        return $this->paginator->nextPageUrl();
    }

    private function url(Users $Users): string
    {
        $direction = $Users === $this->sort ? $this->direction->opposite() : SortDirection::asc;

        return $this->action().'?'.http_build_query(array_filter([
            UsersRequest::search => $this->search,
            UsersRequest::sort => $Users->value,
            UsersRequest::direction => $direction->value,
        ], static fn (string $value): bool => $value !== ''));
    }
}
