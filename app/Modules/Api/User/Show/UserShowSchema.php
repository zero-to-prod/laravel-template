<?php

namespace App\Modules\Api\User\Show;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class UserShowSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::user->value => [
                    'get' => [
                        'operationId' => 'apiUser',
                        'summary' => 'Retrieve the authenticated user.',
                        'tags' => ['User'],
                        'security' => [[SharedSchema::bearer => []]],
                        'responses' => [
                            '200' => [
                                'description' => 'The authenticated user.',
                                'content' => [
                                    'application/json' => ['schema' => UserShowResponse::schema()],
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
