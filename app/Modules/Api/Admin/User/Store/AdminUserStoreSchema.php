<?php

namespace App\Modules\Api\Admin\User\Store;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminUserStoreSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_users->value => [
                    'post' => [
                        'operationId' => 'storeAdminUser',
                        'summary' => 'Create a user.',
                        'tags' => ['Admin Users'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => AdminUserStoreRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'The created user.',
                                'content' => [
                                    'application/json' => ['schema' => AdminUserStoreResponse::schema()],
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
                            '422' => [
                                'description' => 'The request body failed validation.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => SharedSchema::api_validation_error],
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
