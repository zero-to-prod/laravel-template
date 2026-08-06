<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;

readonly class LogoutSchema
{
    public const array schema = [
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
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
                                        'properties' => [
                                            ApiResponse::success => ['type' => 'boolean', 'enum' => [true]],
                                            ApiResponse::message => ['type' => 'string'],
                                            ApiResponse::type => ['type' => 'string', 'enum' => ['Logout']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => 'The token was missing, expired or unrecognised. Produced by the auth:sanctum middleware, so it does not use the standard error envelope.',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['message'],
                                        'properties' => [
                                            'message' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}
