<?php

namespace App\Modules\Api\Login;

use App\Modules\Api\Models\ApiTokenResponse;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\ResponseSchema;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class ApiLoginSchema implements DescribesOperation
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
                ApiRoute::login->value => [
                    'post' => [
                        'operationId' => 'apiLogin',
                        'summary' => 'Authenticate and receive an API token.',
                        'tags' => ['Authentication'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => ApiLoginRequest::rules()],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'The API token.',
                                'content' => [
                                    'application/json' => ['schema' => ResponseSchema::ok(ApiTokenResponse::class)],
                                ],
                            ],
                            '401' => [
                                'description' => 'The credentials did not match a user.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => SharedSchema::api_error],
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
