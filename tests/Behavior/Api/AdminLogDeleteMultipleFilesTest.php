<?php

use App\Modules\Api\Log\DeleteMultipleFiles\AdminLogDeleteMultipleFilesRequest;
use App\Modules\Api\Log\DeleteMultipleFiles\AdminLogDeleteMultipleFilesResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('delete multiple log files', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->postJson(Admin::api_logs_delete_multiple_files->value, [
        AdminLogDeleteMultipleFilesRequest::files => [],
    ]);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogDeleteMultipleFilesResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->postJson(Admin::api_logs_delete_multiple_files->value, [
        AdminLogDeleteMultipleFilesRequest::files => [],
    ]);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('an invalid request body is rejected', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->postJson(Admin::api_logs_delete_multiple_files->value, [
        AdminLogDeleteMultipleFilesRequest::files => [1],
    ]);

    $Response->assertStatus(422);
});
