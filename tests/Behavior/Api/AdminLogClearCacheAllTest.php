<?php

use App\Modules\Api\Log\ClearCacheAll\AdminLogClearCacheAllResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;

test('clear every log file cache', function (): void {
    $User = adminUser();

    $Response = $this->actingAs($User)->postJson(Admin::api_logs_clear_cache_all->value);

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogClearCacheAllResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->postJson(Admin::api_logs_clear_cache_all->value);

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
