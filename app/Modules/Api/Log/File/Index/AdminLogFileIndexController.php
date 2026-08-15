<?php

namespace App\Modules\Api\Log\File\Index;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;

readonly class AdminLogFileIndexController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogFileIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $files = app(FilesController::class)->index($Request)->toArray($Request);

        return api_response()->ok(AdminLogFileIndexResponse::from([
            AdminLogFileIndexResponse::files => $files,
        ]));
    }
}
