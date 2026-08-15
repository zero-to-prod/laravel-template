<?php

namespace App\Modules\Api\Log\File\DownloadRequest;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;

readonly class AdminLogFileDownloadRequestController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFileDownloadRequestSchema::schema();
    })]
    public function __invoke(Request $Request, string $file_identifier): JsonResponse
    {
        $Response = app(FilesController::class)->requestDownload($Request, $file_identifier);

        return api_response()->ok(AdminLogFileDownloadRequestResponse::from([
            AdminLogFileDownloadRequestResponse::url => $Response->getData(true)['url'],
        ]));
    }
}
