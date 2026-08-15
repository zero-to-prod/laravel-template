<?php

use App\Modules\Api\Log\File\FileIdentifierParameter;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFile;

test('download a log file', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-download.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.INFO: download test\n");
    LogViewer::clearFileCache();
    $File = LogViewer::getFiles()->firstWhere('path', $path);
    expect($File)->toBeInstanceOf(LogFile::class);
    $identifier = $File instanceof LogFile ? $File->identifier : '';

    try {
        $Response = $this->actingAs($User)->get(Admin::api_logs_file_download->url([FileIdentifierParameter::name => $identifier]));

        $this->assertMatchesSchema($Response)
            ->assertOk()
            ->assertHeader('content-disposition');
    } finally {
        unlink($path);
    }
});

test('an unauthenticated request is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_file_download->url([FileIdentifierParameter::name => 'example']));

    $this->assertMatchesSchema($Response)->assertStatus(401);
});
