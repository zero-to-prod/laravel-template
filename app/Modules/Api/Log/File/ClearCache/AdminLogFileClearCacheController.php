<?php

namespace App\Modules\Api\Log\File\ClearCache;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;

readonly class AdminLogFileClearCacheController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFileClearCacheSchema::schema();
    })]
    public function __invoke(Request $Request, string $file_identifier): JsonResponse
    {
        app(FilesController::class)->clearCache($file_identifier);

        return api_response()->ok(AdminLogFileClearCacheResponse::from([
            AdminLogFileClearCacheResponse::success => true,
        ]));
    }
}
