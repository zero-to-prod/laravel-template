<?php

use App\Modules\Api\Log\File\Delete\AdminLogFileDeleteResponse;
use App\Modules\Api\Log\File\FileIdentifierParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('delete a log file', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->deleteJson(Admin::api_logs_file->url([FileIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFileDeleteResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->deleteJson(Admin::api_logs_file->url([FileIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
