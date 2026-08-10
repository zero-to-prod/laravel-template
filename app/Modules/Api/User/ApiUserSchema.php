<?php

namespace App\Modules\Api\User;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class ApiUserSchema implements DescribesOperation
{
    /**
     * @return array{paths?: array<string, PathItem>, components?: Components}
     *
     * @throws ReflectionException
     */
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
                                    'application/json' => ['schema' => ApiUserResponse::schema()],
                                ],
                            ],
                            '401' => [
                                'description' => 'The token was missing, expired or unrecognised. Produced by the auth:sanctum middleware, so it does not use the standard error envelope.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            Schema::type => Schema::object,
                                            Schema::required => ['message'],
                                            Schema::properties => [
                                                'message' => [Property::type => Property::string],
                                            ],
                                        ],
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
