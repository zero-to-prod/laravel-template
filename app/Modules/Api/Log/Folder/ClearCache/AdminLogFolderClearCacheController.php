<?php

namespace App\Modules\Api\Log\Folder\ClearCache;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FoldersController;

readonly class AdminLogFolderClearCacheController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFolderClearCacheSchema::schema();
    })]
    public function __invoke(Request $Request, string $folder_identifier): JsonResponse
    {
        app(FoldersController::class)->clearCache($folder_identifier);

        return api_response()->ok(AdminLogFolderClearCacheResponse::from([
            AdminLogFolderClearCacheResponse::success => true,
        ]));
    }
}
