<?php

namespace App\Modules\Api\Log\Index;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminLogIndexSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_logs->value => [
                    'get' => [
                        'operationId' => 'listLogs',
                        'summary' => 'Search and paginate log entries.',
                        'tags' => ['Logs'],
                        'responses' => [
                            '200' => [
                                'description' => 'The successful response.',
                                'content' => [
                                    'application/json' => ['schema' => AdminLogIndexResponse::schema()],
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
