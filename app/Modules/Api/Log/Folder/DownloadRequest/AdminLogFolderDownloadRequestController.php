<?php

namespace App\Modules\Api\Log\Folder\DownloadRequest;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FoldersController;

readonly class AdminLogFolderDownloadRequestController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFolderDownloadRequestSchema::schema();
    })]
    public function __invoke(Request $Request, string $folder_identifier): JsonResponse
    {
        $Response = app(FoldersController::class)->requestDownload($Request, $folder_identifier);

        return api_response()->ok(AdminLogFolderDownloadRequestResponse::from([
            AdminLogFolderDownloadRequestResponse::url => $Response->getData(true)['url'],
        ]));
    }
}
