<?php

use App\Modules\Api\Log\Folder\FolderIdentifierParameter;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFolder;

test('download a log folder archive', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-folder-download.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.INFO: folder download test\n");
    LogViewer::clearFileCache();
    $Folder = LogViewer::getFilesGroupedByFolder()->first();
    expect($Folder)->toBeInstanceOf(LogFolder::class);
    $identifier = $Folder instanceof LogFolder ? $Folder->identifier : '';

    try {
        $Response = $this->actingAs($User)->get(Admin::api_logs_folder_download->url([FolderIdentifierParameter::name => $identifier]));
    } finally {
        unlink($path);
    }

    $this->assertMatchesSchema($Response)
        ->assertOk()
        ->assertHeader('content-disposition');
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_folder_download->url([FolderIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
