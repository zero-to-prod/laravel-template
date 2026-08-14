<?php

use App\Helpers\Role;
use App\Helpers\Theme;
use App\Models\User;
use App\Modules\Admin\Users\Update\UsersUpdateRequest;
use App\Routes\Admin;
use App\Routes\Web;
use App\Sources\Db\App\Users;
use Illuminate\Support\Facades\Hash;

function editUrl(User $User): string
{
    return Admin::user->url([Admin::userParameter => $User->id]);
}

/**
 * @param  array<string, string|bool>  $overrides
 * @return array<string, string|bool>
 */
function payload(User $User, array $overrides = []): array
{
    return [
        UsersUpdateRequest::name => $User->name,
        UsersUpdateRequest::email => $User->email,
        UsersUpdateRequest::verified => $User->email_verified_at !== null,
        UsersUpdateRequest::admin => $User->hasRole(Role::admin->value),
        UsersUpdateRequest::theme => $User->theme->value,
        ...$overrides,
    ];
}

test('guests are redirected to login from the page and the form', function (): void {
    $User = User::factory()->createOne();

    $this->get(editUrl($User))->assertRedirect(Web::login->value);
    $this->post(editUrl($User), payload($User))->assertRedirect(Web::login->value);
});

test('a user without the admin role is forbidden the page and the form', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs($User)->get(editUrl($User))->assertForbidden();
    $this->actingAs($User)->post(editUrl($User), payload($User))->assertForbidden();
});

test('the page renders the account it edits', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->get(editUrl($User))
        ->assertOk()
        ->assertSee($User->name)
        ->assertSee('value="'.$User->email.'"', false)
        ->assertSee('Administrator')
        ->assertSee($User->id)
        ->assertSee('Record details')
        ->assertSee('Authentication providers')
        ->assertSee('Delete user');
});

test('the index links to the page for a listed user', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->get(Admin::users->value)
        ->assertOk()
        ->assertSee(editUrl($User));
});

test('an unknown user is not found', function (): void {
    $this->actingAs(adminUser())
        ->get(Admin::user->url([Admin::userParameter => 'nobody']))
        ->assertNotFound();

    $this->actingAs(adminUser())
        ->post(Admin::user->url([Admin::userParameter => 'nobody']), [
            UsersUpdateRequest::name => 'Ada Lovelace',
            UsersUpdateRequest::email => 'ada@example.com',
        ])
        ->assertNotFound();
});

test('the name and email are saved', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [
            UsersUpdateRequest::name => 'Ada Lovelace',
            UsersUpdateRequest::email => 'ada@example.com',
        ]))
        ->assertRedirect(editUrl($User))
        ->assertSessionHas('status');

    $this->assertDatabaseHas(Users::table(), [
        Users::id->value => $User->getKey(),
        Users::name->value => 'Ada Lovelace',
        Users::email->value => 'ada@example.com',
    ]);
});

test('the theme and an optional new password are saved', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->post(editUrl($User), payload($User, [
            UsersUpdateRequest::theme => Theme::dark->value,
            UsersUpdateRequest::password => 'new-password-1234',
            UsersUpdateRequest::password_confirmation => 'new-password-1234',
        ]))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->theme)->toBe(Theme::dark)
        ->and(Hash::check('new-password-1234', $User->password))->toBeTrue();
});

test('an invalid theme and mismatched password are refused', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->post(editUrl($User), payload($User, [
            UsersUpdateRequest::theme => 'sepia',
            UsersUpdateRequest::password => 'new-password-1234',
            UsersUpdateRequest::password_confirmation => 'mismatch',
        ]))
        ->assertSessionHasErrors([UsersUpdateRequest::theme, UsersUpdateRequest::password]);
});

test('an email another account holds is refused', function (): void {
    $User = User::factory()->createOne();
    $Other = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::email => $Other->email]))
        ->assertRedirect(editUrl($User))
        ->assertSessionHasErrors(UsersUpdateRequest::email);

    expect($User->refresh()->email)->not->toBe($Other->email);
});

test('an account keeps its own email', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::name => 'Ada Lovelace']))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->name)->toBe('Ada Lovelace');
});

test('a name the column will not hold is refused', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::name => '']))
        ->assertSessionHasErrors(UsersUpdateRequest::name);
});

test('clearing the verification sends the user back to confirming their address', function (): void {
    $User = User::factory()->createOne();

    expect($User->email_verified_at)->not->toBeNull();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::verified => false]))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->email_verified_at)->toBeNull();
});

test('verifying an unverified account stamps it now', function (): void {
    $User = User::factory()->unverified()->createOne();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::verified => true]))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->email_verified_at)->not->toBeNull();
});

test('a verification already held is left where it stands', function (): void {
    $verified = now()->subMonth();
    $User = User::factory()->createOne([Users::email_verified_at->value => $verified]);

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::verified => true]))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->email_verified_at?->toDateTimeString())->toBe($verified->toDateTimeString());
});

test('the admin role is granted and revoked', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::admin => true]))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->hasRole(Role::admin->value))->toBeTrue();

    $this->actingAs(adminUser())
        ->from(editUrl($User))
        ->post(editUrl($User), payload($User, [UsersUpdateRequest::admin => false]))
        ->assertSessionHasNoErrors();

    expect($User->refresh()->hasRole(Role::admin->value))->toBeFalse();
});

// Revoking it from the account making the request is the one change that cannot be
// undone from these pages, because the page that undoes it is behind the role.
test('an administrator cannot revoke their own role', function (): void {
    $Admin = adminUser();

    $this->actingAs($Admin)
        ->from(editUrl($Admin))
        ->post(editUrl($Admin), payload($Admin, [UsersUpdateRequest::admin => false]))
        ->assertRedirect(editUrl($Admin))
        ->assertSessionHasErrors(UsersUpdateRequest::admin);

    expect($Admin->refresh()->hasRole(Role::admin->value))->toBeTrue();
});

test('the form repopulates what was submitted when it is refused', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser());

    $this->from(editUrl($User))
        ->post(editUrl($User), payload($User, [
            UsersUpdateRequest::name => '',
            UsersUpdateRequest::email => 'ada@example.com',
        ]));

    $this->get(editUrl($User))
        ->assertOk()
        ->assertSee('value="ada@example.com"', false);
});
