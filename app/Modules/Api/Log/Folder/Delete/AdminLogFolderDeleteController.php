<?php

namespace App\Modules\Api\Log\Folder\Delete;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FoldersController;

readonly class AdminLogFolderDeleteController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFolderDeleteSchema::schema();
    })]
    public function __invoke(Request $Request, string $folder_identifier): JsonResponse
    {
        app(FoldersController::class)->delete($folder_identifier);

        return api_response()->ok(AdminLogFolderDeleteResponse::from([
            AdminLogFolderDeleteResponse::success => true,
        ]));
    }
}
