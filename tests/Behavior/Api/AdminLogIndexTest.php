<?php

use App\Modules\Api\Log\Index\AdminLogIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFile;

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

test('large duplicated context is excluded unless it is requested', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-api-index.log');
    file_put_contents($path, "[2026-08-15 00:00:00] local.ERROR: compact response {\"exception\":\"stack trace\",\"request_id\":\"123\"}\n");
    LogViewer::clearFileCache();
    $File = LogViewer::getFiles()->firstWhere('path', $path);
    expect($File)->toBeInstanceOf(LogFile::class);
    $identifier = $File instanceof LogFile ? $File->identifier : '';

    try {
        $CompactResponse = $this->actingAs($User)->getJson(Admin::api_logs->value.'?'.http_build_query([
            'file' => $identifier,
            'per_page' => 1,
        ]));
        $FullResponse = $this->actingAs($User)->getJson(Admin::api_logs->value.'?'.http_build_query([
            'file' => $identifier,
            'per_page' => 1,
            'include_context' => true,
        ]));
    } finally {
        unlink($path);
    }

    $CompactResponse->assertOk()
        ->assertJsonMissingPath('data.logs.0.full_text')
        ->assertJsonMissingPath('data.logs.0.context.exception')
        ->assertJsonPath('data.logs.0.context.request_id', '123');
    $FullResponse->assertOk()
        ->assertJsonPath('data.logs.0.full_text', 'compact response')
        ->assertJsonPath('data.logs.0.context.exception', 'stack trace');
});
