<?php

namespace App\Modules\Api\Admin\User\Index;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminUserIndexSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_users->value => [
                    'get' => [
                        'operationId' => 'listAdminUsers',
                        'summary' => 'List users.',
                        'tags' => ['Admin Users'],
                        'parameters' => [...PaginationParameters::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The paginated users.',
                                'content' => [
                                    'application/json' => ['schema' => AdminUserIndexResponse::schema()],
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
