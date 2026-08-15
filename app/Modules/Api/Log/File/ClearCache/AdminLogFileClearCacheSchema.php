<?php

namespace App\Modules\Api\Log\File\ClearCache;

use App\Modules\Api\Log\File\FileIdentifierParameter;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminLogFileClearCacheSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_logs_file_clear_cache->value => [
                    'post' => [
                        'operationId' => 'clearLogFileCache',
                        'summary' => 'Clear a log file cache.',
                        'tags' => ['Logs'],
                        'parameters' => [FileIdentifierParameter::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The successful response.',
                                'content' => [
                                    'application/json' => ['schema' => AdminLogFileClearCacheResponse::schema()],
                                ],
                            ],
                            '404' => [
                                'description' => 'The log file was not found.',
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
