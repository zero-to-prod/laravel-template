<?php

namespace App\Modules\Api\Log\Folder\DownloadRequest;

use App\Modules\Api\Log\Folder\FolderIdentifierParameter;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminLogFolderDownloadRequestSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_logs_folder_download_request->value => [
                    'get' => [
                        'operationId' => 'requestLogFolderDownload',
                        'summary' => 'Request a temporary log folder download URL.',
                        'tags' => ['Logs'],
                        'parameters' => [FolderIdentifierParameter::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The successful response.',
                                'content' => [
                                    'application/json' => ['schema' => AdminLogFolderDownloadRequestResponse::schema()],
                                ],
                            ],
                            '404' => [
                                'description' => 'The log folder was not found.',
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
