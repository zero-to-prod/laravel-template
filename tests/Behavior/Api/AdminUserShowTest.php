<?php

use App\Models\User;
use App\Modules\Api\Admin\User\Show\AdminUserShowResponse;
use App\Modules\Api\Admin\User\UserParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('show a user', function (): void {
    $User = adminUser();
    $ManagedUser = User::factory()->createOne(['name' => 'Managed User']);

    $Response = $this->actingAs($User)->getJson(Admin::api_user->url([UserParameter::name => $ManagedUser->id]));

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminUserShowResponse::class),
        ])
        ->assertJsonPath('data.id', $ManagedUser->id)
        ->assertJsonPath('data.name', 'Managed User');
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_user->url([UserParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('the endpoint answers 404', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->getJson(Admin::api_user->url([UserParameter::name => 'missing']));

    $this->assertMatchesSchema($Response)->assertStatus(404);
});
