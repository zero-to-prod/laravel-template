<?php

namespace App\Modules\Api\Login;

use App\DataModels\Fields\GenericEmail;
use App\Modules\Api\Models\ApiToken;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;

readonly class ApiLoginSchema
{
    public const array schema = [
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
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        ApiLoginRequest::email => ['type' => 'string', 'description' => GenericEmail::comment],
                                        ApiLoginRequest::password => ['type' => 'string', 'description' => 'User password'],
                                        ApiLoginRequest::device_name => ['type' => 'string', 'description' => 'Name of the requesting device'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'The API token.',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => [ApiResponse::success, ApiResponse::message, ApiResponse::data, ApiResponse::type],
                                        'properties' => [
                                            ApiResponse::success => ['type' => 'boolean', 'enum' => [true]],
                                            ApiResponse::message => ['type' => 'string'],
                                            ApiResponse::data => [
                                                'type' => 'object',
                                                'required' => [ApiToken::token],
                                                'properties' => [
                                                    ApiToken::token => ['type' => 'string', 'description' => 'API authentication token'],
                                                ],
                                            ],
                                            ApiResponse::type => ['type' => 'string', 'enum' => ['ApiToken']],
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
