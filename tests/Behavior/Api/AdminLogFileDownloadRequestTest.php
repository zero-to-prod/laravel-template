<?php

use App\Modules\Api\Log\File\DownloadRequest\AdminLogFileDownloadRequestResponse;
use App\Modules\Api\Log\File\FileIdentifierParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFile;

test('request a temporary log file download URL', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-file-download-request.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.INFO: file download request test\n");
    LogViewer::clearFileCache();
    $File = LogViewer::getFiles()->firstWhere('path', $path);
    expect($File)->toBeInstanceOf(LogFile::class);
    $identifier = $File instanceof LogFile ? $File->identifier : '';

    try {
        $Response = $this->actingAs($User)->getJson(Admin::api_logs_file_download_request->url([FileIdentifierParameter::name => $identifier]));
    } finally {
        unlink($path);
    }

    $this->assertMatchesSchema($Response)
        ->assertStatus(200)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogFileDownloadRequestResponse::class),
        ])
        ->assertJsonPath('data.url', fn (mixed $url): bool => is_string($url) && str_contains($url, '/admin/logs/api/files/'));
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_file_download_request->url([FileIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});

test('a download cannot be requested for a missing log file', function (): void {
    $User = adminUser();
    $Response = $this->actingAs($User)->getJson(Admin::api_logs_file_download_request->url([FileIdentifierParameter::name => 'missing']));

    $this->assertMatchesSchema($Response)->assertNotFound();
});
