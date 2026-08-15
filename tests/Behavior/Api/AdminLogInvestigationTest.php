<?php

use App\Modules\Api\Admin\LogInvestigation\AdminLogInvestigationParameters;
use App\Modules\Api\Admin\LogInvestigation\AdminLogInvestigationResponse;
use App\Modules\Api\Admin\LogInvestigation\LogInvestigator;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\Http\Controllers\LogsController;
use Opcodes\LogViewer\LogFile;

test('investigate logs through the admin api', function (): void {
    $User = adminUser();
    $path = storage_path('logs/admin-log-investigation.log');
    file_put_contents($path, implode('', [
        "[2026-08-15 00:00:00] local.ERROR: Order 123 failed {\"exception\":\"[object] (RuntimeException: first trace)\"}\n",
        "[2026-08-15 00:01:00] local.ERROR: Order 456 failed {\"exception\":\"[object] (RuntimeException: second trace)\"}\n",
    ]));
    LogViewer::clearFileCache();
    $File = LogViewer::getFiles()->firstWhere('path', $path);
    expect($File)->toBeInstanceOf(LogFile::class);

    try {
        $Response = $this->actingAs($User)->getJson(Admin::api_logs_investigate->value.'?'.http_build_query([
            'file' => $File instanceof LogFile ? $File->identifier : '',
            'query' => 'Order .* failed',
        ]));
        $ContextResponse = $this->actingAs($User)->getJson(Admin::api_logs_investigate->value.'?'.http_build_query([
            'file' => $File instanceof LogFile ? $File->identifier : '',
            'query' => 'Order .* failed',
            'include_context' => true,
        ]));
    } finally {
        unlink($path);
    }

    $this->assertMatchesSchema($Response)
        ->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(AdminLogInvestigationResponse::class),
            'data' => [
                'summary' => ['matches' => 2, 'groups' => 1],
                'findings' => [['occurrences' => 2]],
            ],
        ])
        ->assertJsonMissingPath('data.findings.0.resource_uri')
        ->assertJsonMissingPath('data.findings.0.context')
        ->assertJsonMissingPath('data.findings.0.full_text');
    $ContextResponse
        ->assertOk()
        ->assertJsonPath('data.findings.0.context.exception', '[object] (RuntimeException: second trace)')
        ->assertJsonPath('data.findings.0.full_text', 'Order 456 failed');
});

test('malformed upstream log entries are ignored', function (): void {
    $this->mock(LogsController::class, function (MockInterface $Mock): void {
        $Expectation = $Mock->expects('index');
        $Expectation->andReturn(response()->json([
            'logs' => [null, ['level' => 'ERROR']],
            'pagination' => null,
            'percentScanned' => 100,
        ]));
    });

    expect(app(LogInvestigator::class)->investigate(['file' => 'invalid']))->toMatchArray([
        'summary' => [
            'files_searched' => 1,
            'entries_scanned' => 2,
            'matches' => 0,
            'groups' => 0,
            'percent_scanned' => 100,
        ],
        'findings' => [],
        'level_counts' => [],
        'next_cursor' => null,
    ]);
});

test('invalid investigation filters are rejected', function (): void {
    $Response = $this->actingAs(adminUser())->getJson(Admin::api_logs_investigate->value.'?'.http_build_query([
        'since' => '2026-08-15T01:00:00Z',
        'until' => '2026-08-15T00:00:00Z',
    ]));

    $this->assertMatchesSchema($Response)->assertUnprocessable();
});

test('mcp query string filters are normalized', function (): void {
    $Request = Request::create(Admin::api_logs_investigate->value, 'GET', [
        'levels' => 'ERROR',
        'environments' => 'local',
        'include_context' => 'true',
    ]);
    $Validator = AdminLogInvestigationParameters::validator($Request);

    expect($Validator->fails())->toBeFalse()
        ->and($Validator->validated())->toMatchArray([
            'levels' => ['ERROR'],
            'environments' => ['local'],
            'include_context' => true,
        ]);
});

test('an unauthenticated investigation is rejected', function (): void {
    $Response = $this->getJson(Admin::api_logs_investigate->value);

    $this->assertMatchesSchema($Response)->assertUnauthorized();
});
