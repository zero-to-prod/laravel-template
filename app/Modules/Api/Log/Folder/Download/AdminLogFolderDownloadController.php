<?php

namespace App\Modules\Api\Log\Folder\Download;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FoldersController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

readonly class AdminLogFolderDownloadController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFolderDownloadSchema::schema();
    })]
    public function __invoke(Request $Request, string $folder_identifier): BinaryFileResponse
    {
        return app(FoldersController::class)->download($folder_identifier);
    }
}
