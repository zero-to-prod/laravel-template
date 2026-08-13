<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class LogoutSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::logout->value => [
                    'post' => [
                        'operationId' => 'apiLogout',
                        'summary' => 'Revoke the current API token.',
                        'tags' => ['Authentication'],
                        'security' => [[SharedSchema::bearer => []]],
                        'responses' => [
                            '200' => [
                                'description' => 'The token was revoked.',
                                'content' => [
                                    'application/json' => ['schema' => LogoutResponse::schema()],
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
