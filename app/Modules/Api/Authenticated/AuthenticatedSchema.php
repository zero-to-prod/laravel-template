<?php

namespace App\Modules\Api\Authenticated;

use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

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
                                        Schema::type => Schema::object,
                                        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
                                        Schema::properties => [
                                            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
                                            ApiResponse::message => [Property::type => Property::string],
                                            ApiResponse::type => [Property::type => Property::string, Property::enum => ['Authorized']],
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
