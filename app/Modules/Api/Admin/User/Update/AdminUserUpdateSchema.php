<?php

namespace App\Modules\Api\Admin\User\Update;

use App\Modules\Api\Admin\User\UserParameter;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminUserUpdateSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_user->value => [
                    'patch' => [
                        'operationId' => 'updateAdminUser',
                        'summary' => 'Update a user.',
                        'tags' => ['Admin Users'],
                        'parameters' => [UserParameter::schema()],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => AdminUserUpdateRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'The updated user.',
                                'content' => [
                                    'application/json' => ['schema' => AdminUserUpdateResponse::schema()],
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
                            '404' => [
                                'description' => 'The user was not found.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => SharedSchema::api_error],
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
