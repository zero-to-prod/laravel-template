<?php

namespace App\Modules\Api\Log\DeleteMultipleFiles;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\FilesController;

readonly class AdminLogDeleteMultipleFilesController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogDeleteMultipleFilesSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = AdminLogDeleteMultipleFilesRequest::validator($Request->all());

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        app(FilesController::class)->deleteMultipleFiles($Request);

        return api_response()->ok(AdminLogDeleteMultipleFilesResponse::from([
            AdminLogDeleteMultipleFilesResponse::success => true,
        ]));
    }
}
