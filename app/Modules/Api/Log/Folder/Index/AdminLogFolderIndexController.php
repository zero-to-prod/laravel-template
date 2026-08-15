<?php

namespace App\Modules\Api\Log\Folder\Index;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FoldersController;

readonly class AdminLogFolderIndexController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFolderIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $folders = app(FoldersController::class)->index($Request)->toArray($Request);

        return api_response()->ok(AdminLogFolderIndexResponse::from([
            AdminLogFolderIndexResponse::folders => $folders,
        ]));
    }
}
