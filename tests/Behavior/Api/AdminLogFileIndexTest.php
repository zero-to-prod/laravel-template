<?php

use App\Modules\Api\Log\File\Index\AdminLogFileIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('list log files', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->getJson(Admin::api_logs_files->value);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFileIndexResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_files->value);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
