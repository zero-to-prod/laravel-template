<?php

namespace App\Modules\Api\Log\File\Download;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

readonly class AdminLogFileDownloadController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFileDownloadSchema::schema();
    })]
    public function __invoke(Request $Request, string $file_identifier): BinaryFileResponse
    {
        return app(FilesController::class)->download($file_identifier);
    }
}
