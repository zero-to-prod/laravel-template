<?php

namespace App\Modules\Api\CacheLocks\Show;

use App\Modules\Api\CacheLocks\KeyParameter;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class CacheLocksShowSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::cache_locks_key->value => [
                    'get' => [
                        'operationId' => 'showCacheLock',
                        'summary' => 'Retrieve one cache lock.',
                        'tags' => ['Cache Locks'],
                        'security' => [[SharedSchema::bearer => []]],
                        'parameters' => [KeyParameter::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The cache lock.',
                                'content' => [
                                    'application/json' => ['schema' => CacheLocksShowResponse::schema()],
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
                                'description' => 'There is no cache lock with that key.',
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
