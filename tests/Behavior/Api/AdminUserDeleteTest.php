<?php

use App\Models\User;
use App\Modules\Api\Admin\User\Delete\AdminUserDeleteResponse;
use App\Modules\Api\Admin\User\UserParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('delete a user', function (): void {
    $User = adminUser();
    $ManagedUser = User::factory()->createOne();

    $Response = $this->actingAs($User)->deleteJson(Admin::api_user->url([UserParameter::name => $ManagedUser->id]));

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminUserDeleteResponse::class),
        ])
        ->assertJsonPath('data.id', $ManagedUser->id);

    $this->assertDatabaseMissing('users', ['id' => $ManagedUser->id]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->deleteJson(Admin::api_user->url([UserParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('the endpoint answers 404', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->deleteJson(Admin::api_user->url([UserParameter::name => 'missing']));

    $this->assertMatchesSchema($Response)->assertStatus(404);
});
