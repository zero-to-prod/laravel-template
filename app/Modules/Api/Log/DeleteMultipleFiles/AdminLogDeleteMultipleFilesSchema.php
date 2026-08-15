<?php

namespace App\Modules\Api\Log\DeleteMultipleFiles;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminLogDeleteMultipleFilesSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_logs_delete_multiple_files->value => [
                    'post' => [
                        'operationId' => 'deleteMultipleLogFiles',
                        'summary' => 'Delete multiple log files.',
                        'tags' => ['Logs'],
                        'requestBody' => [
                            'required' => false,
                            'content' => [
                                'application/json' => ['schema' => AdminLogDeleteMultipleFilesRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'The successful response.',
                                'content' => [
                                    'application/json' => ['schema' => AdminLogDeleteMultipleFilesResponse::schema()],
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
