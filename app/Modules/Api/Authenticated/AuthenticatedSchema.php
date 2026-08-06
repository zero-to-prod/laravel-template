<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;

readonly class AuthenticatedSchema
{
    public const array schema = [
        'components' => SharedSchema::components,
        'paths' => [
            ApiRoute::authenticated->value => [
                'get' => [
                    'operationId' => 'apiAuthenticated',
                    'summary' => 'Check if the current token is valid.',
                    'tags' => ['Authentication'],
                    'security' => [[SharedSchema::bearer => []]],
                    'responses' => [
                        '200' => [
                            'description' => 'The token is valid.',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
                                        'properties' => [
                                            ApiResponse::success => ['type' => 'boolean', 'enum' => [true]],
                                            ApiResponse::message => ['type' => 'string'],
                                            ApiResponse::type => ['type' => 'string', 'enum' => ['Authorized']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'The token was missing, expired or unrecognised.',
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
