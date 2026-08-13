<?php

namespace App\Modules\Api\CacheLocks\Index;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class CacheLocksIndexSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::cache_locks->value => [
                    'get' => [
                        'operationId' => 'listCacheLocks',
                        'summary' => 'List the cache locks.',
                        'tags' => ['Cache Locks'],
                        'security' => [[SharedSchema::bearer => []]],
                        'parameters' => [...PaginationParameters::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The cache locks.',
                                'content' => [
                                    'application/json' => ['schema' => CacheLocksIndexResponse::schema()],
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
