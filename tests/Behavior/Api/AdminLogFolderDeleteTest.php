<?php

use App\Modules\Api\Log\Folder\Delete\AdminLogFolderDeleteResponse;
use App\Modules\Api\Log\Folder\FolderIdentifierParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('delete a log folder', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->deleteJson(Admin::api_logs_folder->url([FolderIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFolderDeleteResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->deleteJson(Admin::api_logs_folder->url([FolderIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
