<?php

namespace App\Modules\Api\Cache\Store;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class CacheStoreSchema implements DescribesOperation
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
                ApiRoute::cache->value => [
                    'post' => [
                        'operationId' => 'storeCacheEntry',
                        'summary' => 'Write a cache entry.',
                        'tags' => ['Cache'],
                        'security' => [[SharedSchema::bearer => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => CacheStoreRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'The written cache entry.',
                                'content' => [
                                    'application/json' => ['schema' => CacheStoreResponse::schema()],
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
