<?php

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Modules\Api\Admin\User\Index\AdminUserIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('list users', function (): void {
    $User = adminUser();
    $ManagedUser = User::factory()->createOne(['name' => 'Managed User']);

    $Response = $this->actingAs($User)->getJson(Admin::api_users->value);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminUserIndexResponse::class),
        ])
        ->assertJsonFragment(['id' => $ManagedUser->id, 'name' => 'Managed User']);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_users->value);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('an administrator token with the endpoint ability can list users', function (): void {
    $User = adminUser();
    $token = $User->createToken('admin-api', [HttpVerb::get->ability(Admin::api_users->value)])->plainTextToken;

    $this->assertMatchesSchema($this->withToken($token)->getJson(Admin::api_users->value))->assertOk();
});
