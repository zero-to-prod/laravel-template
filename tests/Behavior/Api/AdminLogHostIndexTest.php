<?php

use App\Modules\Api\Log\Host\Index\AdminLogHostIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('list configured log hosts', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->getJson(Admin::api_logs_hosts->value);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogHostIndexResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_hosts->value);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
