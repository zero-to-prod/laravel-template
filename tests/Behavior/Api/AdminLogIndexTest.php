<?php

use App\Modules\Api\Log\Index\AdminLogIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('search and paginate log entries', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->getJson(Admin::api_logs->value);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogIndexResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs->value);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
