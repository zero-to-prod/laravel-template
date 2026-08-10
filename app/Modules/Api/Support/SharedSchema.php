<?php

namespace App\Modules\Api\Support;

use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class SharedSchema
{
    public const string bearer = 'bearer';
    public const string api_error = '#/components/schemas/ApiError';
    public const string api_validation_error = '#/components/schemas/ApiValidationError';
    public const string middleware_error = '#/components/schemas/MiddlewareError';

    /**
     * The description a 401 declared with `middleware_error` carries. The
     * status comes from auth:sanctum rather than from a controller, so it is
     * the one error the envelope does not describe.
     */
    public const string middleware_error_description = 'The token was missing, expired or unrecognised. Produced by the auth:sanctum middleware, so it does not use the standard error envelope.';

    public const array components = [
        'securitySchemes' => [
            self::bearer => ['type' => 'http', 'scheme' => 'bearer'],
        ],
        'schemas' => [
            'MiddlewareError' => [
                Schema::type => Schema::object,
                Schema::required => [ApiResponse::message],
                Schema::properties => [
                    ApiResponse::message => [Property::type => Property::string],
                ],
            ],
            'ApiError' => [
                Schema::type => Schema::object,
                Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
                Schema::properties => [
                    ApiResponse::success => [Property::type => Property::boolean, Property::enum => [false]],
                    ApiResponse::message => [Property::type => Property::string],
                    ApiResponse::errors => [Property::type => Schema::array, Schema::items => [Property::type => Property::string]],
                    ApiResponse::type => [Property::type => Property::string, Property::enum => ['error']],
                ],
            ],
            'ApiValidationError' => [
                Schema::type => Schema::object,
                Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::errors, ApiResponse::type],
                Schema::properties => [
                    ApiResponse::success => [Property::type => Property::boolean, Property::enum => [false]],
                    ApiResponse::message => [Property::type => Property::string],
                    ApiResponse::errors => [
                        Property::type => Schema::object,
                        Schema::additionalProperties => [
                            Property::type => Schema::array,
                            Schema::items => [Property::type => Property::string],
                        ],
                    ],
                    ApiResponse::type => [Property::type => Property::string, Property::enum => ['error']],
                ],
            ],
        ],
    ];
}
