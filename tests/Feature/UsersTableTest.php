<?php

use App\Helpers\SortDirection;
use App\Models\User;
use App\Modules\Admin\Users\UsersQuery;
use App\Modules\Admin\Users\UsersRequest;
use App\Routes\Admin;
use App\Sources\Db\App\Users;
use App\View\DataModels\SortableHeader;
use App\View\DataModels\TextInput;
use App\View\DataModels\UserRow;
use App\View\DataModels\UsersTable;
use App\View\ViewDirectory;
use Illuminate\Pagination\LengthAwarePaginator;
use Zerotoprod\DataModel\PropertyRequiredException;

/** @param  array<string, mixed>  $overrides */
function usersTable(array $overrides = []): UsersTable
{
    return UsersTable::from([
        UsersTable::search => '',
        UsersTable::sort => Users::name,
        UsersTable::direction => SortDirection::asc,
        UsersTable::paginator => new LengthAwarePaginator([], 0, UsersQuery::perPage),
        ...$overrides,
    ]);
}

test('every property is required', function (): void {
    UsersTable::from([UsersTable::search => '', UsersTable::sort => Users::name]);
})->throws(PropertyRequiredException::class);

test('every column it lists is a real column of the table', function (): void {
    foreach (UsersTable::columns() as $Column) {
        expect(Users::tryFrom($Column->value))->toBe($Column);
    }
});

test('every column it lists is readable off a row', function (): void {
    $properties = array_keys(get_class_vars(UserRow::class));

    foreach (UsersTable::columns() as $Column) {
        expect($properties)->toContain($Column->value);
    }
});

test('a heading carries the column comment as its title', function (): void {
    $SortableHeader = usersTable()->headers()[0];

    expect($SortableHeader->label)->toBe('Name')
        ->and($SortableHeader->title)->toBe(Users::name->comment());
});

test('the heading being ordered by is marked and links to the opposite', function (): void {
    $headers = usersTable([UsersTable::sort => Users::email, UsersTable::direction => SortDirection::desc])->headers();

    $email = collect($headers)->firstOrFail(
        static fn (SortableHeader $SortableHeader): bool => $SortableHeader->label === 'Email'
    );

    expect($email->sorted)->toBeTrue()
        ->and($email->ariaSort())->toBe(SortDirection::desc->aria())
        ->and($email->url)->toContain(UsersRequest::direction.'='.SortDirection::asc->value);
});

test('an unordered heading links ascending and is unmarked', function (): void {
    $headers = usersTable([UsersTable::sort => Users::email])->headers();

    $name = collect($headers)->firstOrFail(
        static fn (SortableHeader $SortableHeader): bool => $SortableHeader->label === 'Name'
    );

    expect($name->sorted)->toBeFalse()
        ->and($name->ariaSort())->toBe('none')
        ->and($name->url)->toContain(UsersRequest::direction.'='.SortDirection::asc->value);
});

test('a heading link carries the search term and drops an empty one', function (): void {
    expect(usersTable([UsersTable::search => 'ada'])->headers()[0]->url)
        ->toContain(UsersRequest::search.'=ada')
        ->and(usersTable()->headers()[0]->url)
        ->not->toContain(UsersRequest::search.'=');
});

test('every heading names an icon that exists', function (): void {
    foreach (usersTable()->headers() as $SortableHeader) {
        expect(ViewDirectory::svg->has($SortableHeader->direction->icon()))->toBeTrue();
    }
});

test('the search box repopulates the term and posts back to the page', function (): void {
    $UsersTable = usersTable([UsersTable::search => 'ada']);
    $TextInput = TextInput::from($UsersTable->searchInput());

    expect($TextInput->name)->toBe(UsersRequest::search)
        ->and($TextInput->value)->toBe('ada')
        ->and($UsersTable->action())->toBe(Admin::users->url())
        ->and($UsersTable->searching())->toBeTrue();
});

test('the form carries the ordering forward so a search does not reset it', function (): void {
    expect(usersTable([UsersTable::sort => Users::email, UsersTable::direction => SortDirection::desc])->hidden())
        ->toBe([
            UsersRequest::sort => Users::email->value,
            UsersRequest::direction => SortDirection::desc->value,
        ]);
});

test('the span covers every column and the actions beside them', function (): void {
    expect(usersTable()->span())->toBe(count(UsersTable::columns()) + 1);
});

test('an empty page summarises as nothing at all', function (): void {
    expect(usersTable()->summary())->toBe('No users')
        ->and(usersTable()->rows())->toBeEmpty()
        ->and(usersTable()->previousUrl())->toBeNull()
        ->and(usersTable()->nextUrl())->toBeNull();
});

test('rows come off the paginator as row models', function (): void {
    $User = User::factory()->createOne();

    $UsersTable = usersTable([
        UsersTable::paginator => new LengthAwarePaginator([$User], 1, UsersQuery::perPage),
    ]);

    expect($UsersTable->rows()[0]->name)->toBe($User->name)
        ->and($UsersTable->summary())->toBe('Showing 1–1 of 1');
});
