<?php

namespace App\Modules\Api\User\Update;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class UserUpdateSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::user->value => [
                    'patch' => [
                        'operationId' => 'updateUserName',
                        'summary' => 'Update the authenticated user name.',
                        'tags' => ['User'],
                        'security' => [[SharedSchema::bearer => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => UserUpdateRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'The updated user.',
                                'content' => [
                                    'application/json' => ['schema' => UserUpdateResponse::schema()],
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
