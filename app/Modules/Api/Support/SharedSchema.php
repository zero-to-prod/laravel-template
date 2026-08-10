<?php

namespace App\Modules\Api\Support;

use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class SharedSchema
{
    public const string bearer = 'bearer';
    public const string api_error = '#/components/schemas/ApiError';
    public const string api_validation_error = '#/components/schemas/ApiValidationError';
    public const array components = [
        'securitySchemes' => [
            self::bearer => ['type' => 'http', 'scheme' => 'bearer'],
        ],
        'schemas' => [
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
                        'additionalProperties' => [
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
