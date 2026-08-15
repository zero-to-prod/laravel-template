<?php

use App\Modules\Api\Log\File\ClearCache\AdminLogFileClearCacheResponse;
use App\Modules\Api\Log\File\FileIdentifierParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFile;

test('clear a log file cache', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-clear-cache.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.INFO: clear cache test\n");
    LogViewer::clearFileCache();
    $File = LogViewer::getFiles()->firstWhere('path', $path);
    expect($File)->toBeInstanceOf(LogFile::class);
    $identifier = $File instanceof LogFile ? $File->identifier : '';

    try {
        $Response = $this->actingAs($User)->postJson(Admin::api_logs_file_clear_cache->url([FileIdentifierParameter::name => $identifier]));
    } finally {
        unlink($path);
    }

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFileClearCacheResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->postJson(Admin::api_logs_file_clear_cache->url([FileIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('a missing log file cache cannot be cleared', function (): void {
    $User = adminUser();
    $Response = $this->actingAs($User)->postJson(Admin::api_logs_file_clear_cache->url([FileIdentifierParameter::name => 'missing']));

    $this->assertMatchesSchema($Response)->assertNotFound();
});
