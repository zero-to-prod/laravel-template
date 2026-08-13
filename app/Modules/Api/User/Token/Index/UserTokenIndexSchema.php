<?php

namespace App\Modules\Api\User\Token\Index;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class UserTokenIndexSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::user_tokens->value => [
                    'get' => [
                        'operationId' => 'listUserTokens',
                        'summary' => 'List the personal access tokens of the authenticated user.',
                        'tags' => ['Tokens'],
                        'security' => [[SharedSchema::bearer => []]],
                        'parameters' => [...PaginationParameters::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The tokens, oldest first.',
                                'content' => [
                                    'application/json' => ['schema' => UserTokenIndexResponse::schema()],
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
                        ],
                    ],
                ],
            ],
        ];
    }
}
