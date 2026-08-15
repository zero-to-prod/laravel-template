<?php

namespace App\Modules\Api\Log\Host\Index;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\HostsController;

readonly class AdminLogHostIndexController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogHostIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $hosts = app(HostsController::class)->index()->toArray($Request);

        return api_response()->ok(AdminLogHostIndexResponse::from([
            AdminLogHostIndexResponse::hosts => $hosts,
        ]));
    }
}
