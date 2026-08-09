<?php

namespace App\Modules\Api\Login;

use App\Modules\Api\Models\ApiToken;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

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
                                    'application/json' => [
                                        'schema' => [
                                            Schema::type => Schema::object,
                                            Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::data, ApiResponse::type],
                                            Schema::properties => [
                                                ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
                                                ApiResponse::message => [Property::type => Property::string],
                                                ApiResponse::data => [
                                                    Property::type => Schema::object,
                                                    Schema::required => [ApiToken::token],
                                                    Schema::properties => [
                                                        ApiToken::token => [Property::type => Property::string, Property::description => 'API authentication token'],
                                                    ],
                                                ],
                                                ApiResponse::type => [Property::type => Property::string, Property::enum => ['ApiToken']],
                                            ],
                                        ],
                                    ],
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
