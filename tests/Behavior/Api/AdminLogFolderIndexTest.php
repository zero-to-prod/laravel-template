<?php

use App\Modules\Api\Log\Folder\Index\AdminLogFolderIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('list log folders', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->getJson(Admin::api_logs_folders->value);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFolderIndexResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_folders->value);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
