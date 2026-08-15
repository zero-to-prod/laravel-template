<?php

namespace App\Modules\Api\Public\Readme;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class ReadmeSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::readme->value => [
                    'get' => [
                        'operationId' => 'apiReadme',
                        'summary' => 'Retrieve the API readme.',
                        'tags' => ['Documentation'],
                        'responses' => [
                            '200' => [
                                'description' => 'The API readme, as markdown.',
                                'content' => [
                                    'application/json' => ['schema' => ReadmeResponse::schema()],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
