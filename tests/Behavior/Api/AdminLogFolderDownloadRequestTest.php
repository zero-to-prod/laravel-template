<?php

use App\Modules\Api\Log\Folder\DownloadRequest\AdminLogFolderDownloadRequestResponse;
use App\Modules\Api\Log\Folder\FolderIdentifierParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFolder;

test('request a temporary log folder download URL', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-folder-download-request.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.INFO: folder download request test\n");
    LogViewer::clearFileCache();
    $Folder = LogViewer::getFilesGroupedByFolder()->first();
    expect($Folder)->toBeInstanceOf(LogFolder::class);
    $identifier = $Folder instanceof LogFolder ? $Folder->identifier : '';

    try {
        $Response = $this->actingAs($User)->getJson(Admin::api_logs_folder_download_request->url([FolderIdentifierParameter::name => $identifier]));
    } finally {
        unlink($path);
    }

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFolderDownloadRequestResponse::class),
        ])
        ->assertJsonPath('data.url', fn (mixed $url): bool => is_string($url) && str_contains($url, '/admin/logs/api/folders/'));
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_folder_download_request->url([FolderIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('a download cannot be requested for a missing log folder', function (): void {
    $User = adminUser();
    $Response = $this->actingAs($User)->getJson(Admin::api_logs_folder_download_request->url([FolderIdentifierParameter::name => 'missing']));

    $this->assertMatchesSchema($Response)->assertNotFound();
});
