<?php

use App\Helpers\Role;
use App\Helpers\SortDirection;
use App\Models\User;
use App\Modules\Admin\Users\UsersQuery;
use App\Modules\Admin\Users\UsersRequest;
use App\Routes\Admin;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use App\View\DataModels\UsersTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

function adminUser(): User
{
    $User = User::factory()->createOne();
    $User->assignRole(Role::admin->value);

    return $User;
}

/** @param  array<string, string|int>  $query */
function usersUrl(array $query = []): string
{
    return $query === [] ? Admin::users->value : Admin::users->value.'?'.http_build_query($query);
}

test('guests are redirected to login', function (): void {
    $this->get(Admin::users->value)
        ->assertRedirect(Web::login->value);
});

test('a user without the admin role is forbidden', function (): void {
    $this->actingAs(User::factory()->createOne())
        ->get(Admin::users->value)
        ->assertForbidden();
});

test('the page lists a user', function (): void {
    $User = adminUser();

    $this->actingAs($User)
        ->get(Admin::users->value)
        ->assertOk()
        ->assertSee('Users')
        ->assertSee($User->name)
        ->assertSee($User->email);
});

test('every column the table lists gets a heading linking to its own ordering', function (): void {
    $TestResponse = $this->actingAs(adminUser())
        ->get(Admin::users->value)
        ->assertOk();

    foreach (UsersTable::columns() as $Column) {
        $TestResponse->assertSee(Str::headline($Column->name))
            ->assertSee(UsersRequest::sort.'='.$Column->value, false);
    }
});

test('the heading already ordered links to the opposite direction', function (): void {
    $this->actingAs(adminUser())
        ->get(usersUrl([
            UsersRequest::sort => Users::email->value,
            UsersRequest::direction => SortDirection::asc->value,
        ]))
        ->assertOk()
        ->assertSee(UsersRequest::direction.'='.SortDirection::desc->value, false);
});

test('the search box filters by name', function (): void {
    $Match = User::factory()->createOne([Users::name->value => 'Ada Lovelace']);
    $Other = User::factory()->createOne([Users::name->value => 'Grace Hopper']);

    $this->actingAs(adminUser())
        ->get(usersUrl([UsersRequest::search => 'Ada Lovelace']))
        ->assertOk()
        ->assertSee($Match->name)
        ->assertDontSee($Other->name);
});

test('the search box filters by email and keeps the term', function (): void {
    $Match = User::factory()->createOne([Users::email->value => 'ada@example.com']);
    $Other = User::factory()->createOne([Users::email->value => 'grace@example.com']);

    $this->actingAs(adminUser())
        ->get(usersUrl([UsersRequest::search => 'ada@example.com']))
        ->assertOk()
        ->assertSee($Match->email)
        ->assertDontSee($Other->email)
        ->assertSee('value="ada@example.com"', false);
});

test('a search matching nothing says so', function (): void {
    $this->actingAs(adminUser())
        ->get(usersUrl([UsersRequest::search => 'nobody-by-that-name']))
        ->assertOk()
        ->assertSee('No users found.');
});

test('an unrecognised ordering falls back rather than reaching the database', function (): void {
    $this->actingAs(adminUser())
        ->get(usersUrl([
            UsersRequest::sort => Users::password->value,
            UsersRequest::direction => 'sideways',
        ]))
        ->assertOk();

    $UsersRequest = UsersRequest::of(Request::create(usersUrl([
        UsersRequest::sort => Users::password->value,
        UsersRequest::direction => 'sideways',
    ])));

    expect($UsersRequest->sort)->toBe(UsersTable::columns()[0])
        ->and($UsersRequest->direction)->toBe(SortDirection::asc);
});

test('the ordering the request asks for is the ordering the query runs', function (): void {
    User::factory()->count(3)->create();

    $UsersRequest = UsersRequest::of(Request::create(usersUrl([
        UsersRequest::sort => Users::email->value,
        UsersRequest::direction => SortDirection::desc->value,
    ])));

    $emails = UsersQuery::get($UsersRequest)->getCollection()->pluck(Users::email->value)->all();

    expect($emails)->toBe(collect($emails)->sortDesc()->values()->all());
});

test('a second page is reachable and keeps the term', function (): void {
    User::factory()->count(UsersQuery::perPage + 1)->create([Users::name->value => 'Paginated Person']);

    $this->actingAs(adminUser())
        ->get(usersUrl([UsersRequest::search => 'Paginated Person', 'page' => 2]))
        ->assertOk()
        ->assertSee('Paginated Person');
});
