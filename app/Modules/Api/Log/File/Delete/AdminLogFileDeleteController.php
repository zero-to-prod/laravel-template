<?php

namespace App\Modules\Api\Log\File\Delete;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;

readonly class AdminLogFileDeleteController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFileDeleteSchema::schema();
    })]
    public function __invoke(Request $Request, string $file_identifier): JsonResponse
    {
        app(FilesController::class)->delete($file_identifier);

        return api_response()->ok(AdminLogFileDeleteResponse::from([
            AdminLogFileDeleteResponse::success => true,
        ]));
    }
}
