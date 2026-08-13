<?php

namespace App\Modules\Api\User\Token\Store;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class UserTokenStoreSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::user_tokens->value => [
                    'post' => [
                        'operationId' => 'createUserToken',
                        'summary' => 'Issue a personal access token for the authenticated user.',
                        'tags' => ['Tokens'],
                        'security' => [[SharedSchema::bearer => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => UserTokenStoreRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'The issued token, carrying the plain text secret once.',
                                'content' => [
                                    'application/json' => ['schema' => UserTokenStoreResponse::schema()],
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
                            '403' => [
                                'description' => SharedSchema::missing_ability_description,
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
