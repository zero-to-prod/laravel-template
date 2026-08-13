<?php

namespace App\Modules\Api\CacheLocks\Store;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class CacheLocksStoreSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::cache_locks->value => [
                    'post' => [
                        'operationId' => 'storeCacheLock',
                        'summary' => 'Write a cache lock.',
                        'tags' => ['Cache Locks'],
                        'security' => [[SharedSchema::bearer => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => CacheLocksStoreRequest::schema()],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'The written cache lock.',
                                'content' => [
                                    'application/json' => ['schema' => CacheLocksStoreResponse::schema()],
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
