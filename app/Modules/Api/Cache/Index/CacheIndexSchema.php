<?php

namespace App\Modules\Api\Cache\Index;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class CacheIndexSchema implements DescribesOperation
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
                    'get' => [
                        'operationId' => 'listCacheEntries',
                        'summary' => 'List the cache entries.',
                        'tags' => ['Cache'],
                        'security' => [[SharedSchema::bearer => []]],
                        'parameters' => [...PaginationParameters::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The cache entries.',
                                'content' => [
                                    'application/json' => ['schema' => CacheIndexResponse::schema()],
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
