<?php

use App\Modules\Api\Admin\User\Store\AdminUserStoreRequest;
use App\Modules\Api\Admin\User\Store\AdminUserStoreResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('create a user', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->postJson(Admin::api_users->value, [
        AdminUserStoreRequest::name => 'Managed User',
        AdminUserStoreRequest::email => 'managed@example.com',
        AdminUserStoreRequest::password => 'secret-password',
    ]);

    $this->assertMatchesSchema($Response)
        ->assertStatus(201)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminUserStoreResponse::class),
        ])
        ->assertJsonPath('data.name', 'Managed User')
        ->assertJsonPath('data.email', 'managed@example.com');

    $this->assertDatabaseHas('users', ['email' => 'managed@example.com']);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->postJson(Admin::api_users->value, [
        AdminUserStoreRequest::name => 'example',
        AdminUserStoreRequest::email => 'managed@example.com',
        AdminUserStoreRequest::password => 'example',
    ]);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('a blank name is rejected', function (): void {
    $User = adminUser();

    // Blank is a server policy, not a published constraint: the document admits
    // the empty string, so the request still conforms and the 422 is reachable.
    $Response = $this->actingAs($User)->postJson(Admin::api_users->value, [
        AdminUserStoreRequest::name => '',
        AdminUserStoreRequest::email => 'managed@example.com',
        AdminUserStoreRequest::password => 'example',
    ]);

    $this->assertMatchesSchema($Response)
        ->assertStatus(422)
        ->assertJsonValidationErrors(AdminUserStoreRequest::name);
});
