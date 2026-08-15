<?php

namespace App\Modules\Api\Log\Index;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Http\Controllers\LogsController;

readonly class AdminLogIndexController
{
    #[AdminApiSchema(static function (): array {
        return AdminLogIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $data = app(LogsController::class)->index($Request)->getData(true);
        $logs = AdminLogIndexParameters::includesContext($Request)
            ? $data['logs']
            : self::withoutContext($data['logs']);

        return api_response()->ok(AdminLogIndexResponse::from([
            AdminLogIndexResponse::file => $data['file'],
            AdminLogIndexResponse::level_counts => $data['levelCounts'],
            AdminLogIndexResponse::logs => $logs,
            AdminLogIndexResponse::columns => $data['columns'],
            AdminLogIndexResponse::pagination => $data['pagination'],
            AdminLogIndexResponse::expand_automatically => $data['expandAutomatically'],
            AdminLogIndexResponse::cache_recently_cleared => $data['cacheRecentlyCleared'],
            AdminLogIndexResponse::has_more_results => $data['hasMoreResults'],
            AdminLogIndexResponse::percent_scanned => $data['percentScanned'],
            AdminLogIndexResponse::performance => $data['performance'],
        ]));
    }

    /**
     * @param  list<array<string, mixed>>  $logs
     * @return list<array<string, mixed>>
     */
    private static function withoutContext(array $logs): array
    {
        return array_map(static function (array $log): array {
            unset($log['full_text']);

            if (isset($log['context']) && is_array($log['context'])) {
                unset($log['context']['exception']);

                if ($log['context'] === []) {
                    unset($log['context']);
                }
            }

            return $log;
        }, $logs);
    }
}
