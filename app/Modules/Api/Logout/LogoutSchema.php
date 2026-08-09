<?php

namespace App\Modules\Api\Logout;

use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

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
                                        Schema::type => Schema::object,
                                        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
                                        Schema::properties => [
                                            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
                                            ApiResponse::message => [Property::type => Property::string],
                                            ApiResponse::type => [Property::type => Property::string, Property::enum => ['Logout']],
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
                                        Schema::type => Schema::object,
                                        Schema::required => ['message'],
                                        Schema::properties => [
                                            'message' => [Property::type => Property::string],
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
