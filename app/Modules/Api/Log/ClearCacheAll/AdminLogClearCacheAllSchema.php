<?php

namespace App\Modules\Api\Log\ClearCacheAll;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminLogClearCacheAllSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_logs_clear_cache_all->value => [
                    'post' => [
                        'operationId' => 'clearAllLogCaches',
                        'summary' => 'Clear every log file cache.',
                        'tags' => ['Logs'],
                        'responses' => [
                            '200' => [
                                'description' => 'The successful response.',
                                'content' => [
                                    'application/json' => ['schema' => AdminLogClearCacheAllResponse::schema()],
                                ],
                            ],
                            '401' => [
                                'description' => SharedSchema::middleware_error_description,
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => SharedSchema::middleware_error],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
