<?php

namespace App\Modules\Api\Log\ClearCacheAll;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;

readonly class AdminLogClearCacheAllController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogClearCacheAllSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        app(FilesController::class)->clearCacheAll();

        return api_response()->ok(AdminLogClearCacheAllResponse::from([
            AdminLogClearCacheAllResponse::success => true,
        ]));
    }
}
