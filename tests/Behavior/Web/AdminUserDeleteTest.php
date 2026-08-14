<?php

use App\Helpers\OauthProviderId;
use App\Helpers\Role;
use App\Models\OauthProvider;
use App\Models\User;
use App\Modules\Admin\Users\Delete\UserDeleteController;
use App\Routes\Admin;
use App\Routes\Web;
use App\Sources\Db\App\OauthProviders;
use App\Sources\Db\App\Users;
// TODO: do not use Illuminate\Support\Facades\DB
use Illuminate\Support\Facades\DB;

function userDeleteUrl(string $userId): string
{
    return Admin::user->url([Admin::userParameter => $userId]);
}

function providerDeleteUrl(User $User, string $providerId): string
{
    return Admin::userProvider->url([
        Admin::userParameter => $User->id,
        Admin::providerParameter => $providerId,
    ]);
}

function providerFor(User $User): OauthProvider
{
    return $User->oauthProviders()->create([
        OauthProviders::provider_id->value => OauthProviderId::google->value,
        OauthProviders::sub->value => 'provider-'.$User->id,
        OauthProviders::name->value => 'Google User',
        OauthProviders::given_name->value => 'Google',
        OauthProviders::family_name->value => 'User',
        OauthProviders::picture->value => 'https://example.com/avatar.jpg',
        OauthProviders::email->value => $User->email,
        OauthProviders::email_verified->value => true,
        OauthProviders::hd->value => 'example.com',
        OauthProviders::id->value => 'provider-'.$User->id,
        OauthProviders::verified_email->value => true,
        OauthProviders::link->value => 'https://example.com/profile',
    ]);
}

test('the page shows every provider detail and its remove action', function (): void {
    $User = User::factory()->createOne();
    $OauthProvider = providerFor($User);

    $this->actingAs(adminUser())
        ->get(userDeleteUrl($User->id))
        ->assertOk()
        ->assertSee('Google User')
        ->assertSee('example.com')
        ->assertSee('provider-'.$User->id)
        ->assertSee('https://example.com/avatar.jpg')
        ->assertSee('https://example.com/profile')
        ->assertSee(providerDeleteUrl($User, $OauthProvider->sub));
});

test('guests and non admins cannot delete a user or provider', function (): void {
    $User = User::factory()->createOne();
    $OauthProvider = providerFor($User);

    $this->delete(userDeleteUrl($User->id))->assertRedirect(Web::login->value);
    $this->delete(providerDeleteUrl($User, $OauthProvider->sub))->assertRedirect(Web::login->value);

    $RegularUser = User::factory()->createOne();
    $this->actingAs($RegularUser)->delete(userDeleteUrl($User->id))->assertForbidden();
    $this->actingAs($RegularUser)->delete(providerDeleteUrl($User, $OauthProvider->sub))->assertForbidden();
});

test('an admin can remove one provider belonging to the user', function (): void {
    $User = User::factory()->createOne();
    $OauthProvider = providerFor($User);

    $this->actingAs(adminUser())
        ->from(userDeleteUrl($User->id))
        ->delete(providerDeleteUrl($User, $OauthProvider->sub))
        ->assertRedirect(userDeleteUrl($User->id))
        ->assertSessionHas('status', 'Sign-in provider removed.');

    expect($OauthProvider->fresh())->toBeNull()
        ->and($User->fresh())->not->toBeNull();
});

test('a provider cannot be removed through another user', function (): void {
    $User = User::factory()->createOne();
    $Other = User::factory()->createOne();
    $OauthProvider = providerFor($User);

    $this->actingAs(adminUser())
        ->delete(providerDeleteUrl($Other, $OauthProvider->sub))
        ->assertNotFound();

    expect($OauthProvider->fresh())->not->toBeNull();
});

test('unknown deletion targets are not found', function (): void {
    $User = User::factory()->createOne();
    $Admin = adminUser();

    $this->actingAs($Admin)->delete(userDeleteUrl('nobody'))->assertNotFound();
    $this->actingAs($Admin)->delete(providerDeleteUrl($User, 'nobody'))->assertNotFound();
    $this->actingAs($Admin)->delete(Admin::userProvider->url([
        Admin::userParameter => 'nobody',
        Admin::providerParameter => 'nobody',
    ]))->assertNotFound();
});

test('an admin cannot delete their own account', function (): void {
    $Admin = adminUser();

    $this->actingAs($Admin)
        ->from(userDeleteUrl($Admin->id))
        ->delete(userDeleteUrl($Admin->id))
        ->assertRedirect(userDeleteUrl($Admin->id))
        ->assertSessionHasErrors('delete');

    expect($Admin->fresh())->not->toBeNull();
});

test('the exact confirmation word is required to delete a user', function (): void {
    $User = User::factory()->createOne();

    $this->actingAs(adminUser())
        ->from(userDeleteUrl($User->id))
        ->delete(userDeleteUrl($User->id), [UserDeleteController::confirmation => 'DELETE'])
        ->assertRedirect(userDeleteUrl($User->id))
        ->assertSessionHasErrors('delete');

    expect($User->fresh())->not->toBeNull();
});

test('deleting a user cascades every related record', function (): void {
    $User = User::factory()->createOne();
    providerFor($User);
    $User->assignRole(Role::admin->value);
    $Token = $User->createToken('Delete me');

    // TODO: do not use Illuminate\Support\Facades\DB
    DB::table('password_reset_tokens')->insert([
        Users::email->value => $User->email,
        'token' => 'hashed-token',
    ]);
    // TODO: do not use Illuminate\Support\Facades\DB
    DB::table('sessions')->insert([
        'id' => 'user-session',
        'user_id' => $User->id,
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs(adminUser())
        ->delete(userDeleteUrl($User->id), [UserDeleteController::confirmation => 'delete'])
        ->assertRedirect(Admin::users->value)
        ->assertSessionHas('status', 'User deleted.');

    expect($User->fresh())->toBeNull();
    $this->assertDatabaseMissing(OauthProviders::table(), [OauthProviders::user_id->value => $User->id]);
    $this->assertDatabaseMissing('model_has_roles', ['model_id' => $User->id]);
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $Token->accessToken->getKey()]);
    $this->assertDatabaseMissing('password_reset_tokens', [Users::email->value => $User->email]);
    $this->assertDatabaseMissing('sessions', ['user_id' => $User->id]);
});
