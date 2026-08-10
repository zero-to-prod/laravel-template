<?php

use App\Modules\Api\Authenticated\AuthenticatedResponse;
use App\Modules\Api\Login\LoginResponse;
use App\Modules\Api\Support\ApiResponse;
use Tests\Fixtures\OasResponseStub;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('the envelope carries the model as data', function (): void {
    expect(LoginResponse::schema())->toBe([
        Schema::type => Schema::object,
        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::data, ApiResponse::type],
        Schema::properties => [
            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
            ApiResponse::message => [Property::type => Property::string],
            ApiResponse::data => [
                Schema::type => Schema::object,
                Schema::required => [LoginResponse::token],
                Schema::properties => [
                    LoginResponse::token => [
                        Property::type => Property::string,
                        Property::description => 'API authentication token',
                    ],
                ],
            ],
            ApiResponse::type => [
                Property::type => Property::string,
                Property::enum => [class_basename(LoginResponse::class)],
            ],
        ],
    ]);
});

test('a model with no properties contributes no data key', function (): void {
    // Api::respond() strips the empty array, so publishing `data` would
    // describe a key the response never carries.
    expect(AuthenticatedResponse::schema())->toBe([
        Schema::type => Schema::object,
        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
        Schema::properties => [
            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
            ApiResponse::message => [Property::type => Property::string],
            ApiResponse::type => [
                Property::type => Property::string,
                Property::enum => [class_basename(AuthenticatedResponse::class)],
            ],
        ],
    ]);
});

test('property types map to their openapi equivalents, nullables are optional, and type tracks the basename', function (): void {
    // The `type` enum has to stay whatever Api::resolveType() would publish,
    // which is the payload's class basename.
    expect(OasResponseStub::schema())->toBe([
        Schema::type => Schema::object,
        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::data, ApiResponse::type],
        Schema::properties => [
            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
            ApiResponse::message => [Property::type => Property::string],
            ApiResponse::data => [
                Schema::type => Schema::object,
                Schema::required => ['name', 'count', 'ratio', 'active', 'tags', 'label', 'empty_schema'],
                Schema::properties => [
                    'name' => [Property::type => Property::string, Property::description => 'The display name'],
                    'count' => [Property::type => Property::integer],
                    'ratio' => [Property::type => Property::number],
                    'active' => [Property::type => Property::boolean],
                    'tags' => [Property::type => Schema::array],
                    // The column's schema, with nullability taken from the
                    // property rather than from the column.
                    'verified_at' => [
                        Property::type => Property::string,
                        Property::format => Property::date_time,
                        Property::description => 'When the users email was verified',
                        Property::nullable => true,
                    ],
                    'label' => [
                        Property::type => Property::string,
                        Property::description => 'The overriding description',
                    ],
                    'empty_schema' => [Property::type => Property::string],
                    // Not required, but the model sends it as null rather than
                    // omitting it, so the schema has to accept null.
                    'nickname' => [Property::type => Property::string, Property::nullable => true],
                ],
            ],
            ApiResponse::type => [
                Property::type => Property::string,
                Property::enum => [class_basename(OasResponseStub::class)],
            ],
        ],
    ]);
});
