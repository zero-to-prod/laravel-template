<?php

namespace App\Modules\Api\Support;

/**
 * OpenAPI components shared by every API endpoint.
 *
 * Referenced from each module's schema so the definitions survive as long as
 * any one endpoint does. `components` merge across attributes, so declaring
 * them everywhere is idempotent.
 *
 * Property keys come from ApiResponse, the model the envelope is built from,
 * so renaming a property there breaks the document at compile time.
 */
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
                'type' => 'object',
                'required' => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
                'properties' => [
                    ApiResponse::success => ['type' => 'boolean', 'enum' => [false]],
                    ApiResponse::message => ['type' => 'string'],
                    ApiResponse::errors => ['type' => 'array', 'items' => ['type' => 'string']],
                    ApiResponse::type => ['type' => 'string', 'enum' => ['error']],
                ],
            ],
            'ApiValidationError' => [
                'type' => 'object',
                'required' => [ApiResponse::success, ApiResponse::message, ApiResponse::errors, ApiResponse::type],
                'properties' => [
                    ApiResponse::success => ['type' => 'boolean', 'enum' => [false]],
                    ApiResponse::message => ['type' => 'string'],
                    ApiResponse::errors => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                    ApiResponse::type => ['type' => 'string', 'enum' => ['error']],
                ],
            ],
        ],
    ];
}
