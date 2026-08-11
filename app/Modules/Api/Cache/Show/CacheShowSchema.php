<?php

namespace App\Modules\Api\Cache\Show;

use App\Modules\Api\Cache\KeyParameter;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class CacheShowSchema implements DescribesOperation
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
                ApiRoute::cache_key->value => [
                    'get' => [
                        'operationId' => 'showCacheEntry',
                        'summary' => 'Retrieve one cache entry.',
                        'tags' => ['Cache'],
                        'security' => [[SharedSchema::bearer => []]],
                        'parameters' => [KeyParameter::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The cache entry.',
                                'content' => [
                                    'application/json' => ['schema' => CacheShowResponse::schema()],
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
                                'description' => 'There is no cache entry with that key.',
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
