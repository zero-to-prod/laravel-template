<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AuthenticatedSchema implements DescribesOperation
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
                ApiRoute::authenticated->value => [
                    'get' => [
                        'operationId' => 'apiAuthenticated',
                        'summary' => 'Check if the current token is valid.',
                        'tags' => ['Authentication'],
                        'security' => [[SharedSchema::bearer => []]],
                        'responses' => [
                            '200' => [
                                'description' => 'The token is valid.',
                                'content' => [
                                    'application/json' => ['schema' => AuthenticatedResponse::schema()],
                                ],
                            ],
                            '401' => [
                                'description' => 'The token was missing, expired or unrecognised.',
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
