<?php

use App\Modules\Api\Log\Folder\ClearCache\AdminLogFolderClearCacheResponse;
use App\Modules\Api\Log\Folder\FolderIdentifierParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFolder;

test('clear cached indexes for a log folder', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-folder-clear-cache.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.INFO: folder clear cache test\n");
    LogViewer::clearFileCache();
    $Folder = LogViewer::getFilesGroupedByFolder()->first();
    expect($Folder)->toBeInstanceOf(LogFolder::class);
    $identifier = $Folder instanceof LogFolder ? $Folder->identifier : '';

    try {
        $Response = $this->actingAs($User)->postJson(Admin::api_logs_folder_clear_cache->url([FolderIdentifierParameter::name => $identifier]));
    } finally {
        unlink($path);
    }

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFolderClearCacheResponse::class),
        ]);
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->postJson(Admin::api_logs_folder_clear_cache->url([FolderIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('a missing log folder cache cannot be cleared', function (): void {
    $User = adminUser();
    $Response = $this->actingAs($User)->postJson(Admin::api_logs_folder_clear_cache->url([FolderIdentifierParameter::name => 'missing']));

    $this->assertMatchesSchema($Response)->assertNotFound();
});
