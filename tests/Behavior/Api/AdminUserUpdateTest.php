<?php

use App\Models\User;
use App\Modules\Api\Admin\User\Update\AdminUserUpdateRequest;
use App\Modules\Api\Admin\User\Update\AdminUserUpdateResponse;
use App\Modules\Api\Admin\User\UserParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('update a user', function (): void {
    $User = adminUser();
    $ManagedUser = User::factory()->createOne();

    $Response = $this->actingAs($User)->patchJson(Admin::api_user->url([UserParameter::name => $ManagedUser->id]), [
        AdminUserUpdateRequest::name => 'Updated User',
    ]);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminUserUpdateResponse::class),
        ])
        ->assertJsonPath('data.name', 'Updated User');

    expect($ManagedUser->refresh()->name)->toBe('Updated User');
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->patchJson(Admin::api_user->url([UserParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('a blank name is rejected', function (): void {
    $User = adminUser();
    $ManagedUser = User::factory()->createOne();

    $Response = $this->actingAs($User)->patchJson(Admin::api_user->url([UserParameter::name => $ManagedUser->id]), [
        AdminUserUpdateRequest::name => '',
    ]);

    $this->assertMatchesSchema($Response)
        ->assertStatus(422)
        ->assertJsonValidationErrors(AdminUserUpdateRequest::name);
});

test('the endpoint answers 404', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->patchJson(Admin::api_user->url([UserParameter::name => 'missing']), [
        AdminUserUpdateRequest::name => 'Missing User',
    ]);

    $this->assertMatchesSchema($Response)->assertStatus(404);
});
