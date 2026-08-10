<?php

use App\Modules\Api\Authenticated\AuthenticatedResponse;
use App\Modules\Api\Login\ApiLoginResponse;
use App\Modules\Api\Support\ApiResponse;
use Tests\Fixtures\OasResponseStub;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('the envelope carries the model as data', function (): void {
    expect(ApiLoginResponse::schema())->toBe([
        Schema::type => Schema::object,
        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::data, ApiResponse::type],
        Schema::properties => [
            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
            ApiResponse::message => [Property::type => Property::string],
            ApiResponse::data => [
                Schema::type => Schema::object,
                Schema::required => [ApiLoginResponse::token],
                Schema::properties => [
                    ApiLoginResponse::token => [
                        Property::type => Property::string,
                        Property::description => 'API authentication token',
                    ],
                ],
            ],
            ApiResponse::type => [
                Property::type => Property::string,
                Property::enum => [class_basename(ApiLoginResponse::class)],
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
                Schema::required => ['name', 'count', 'ratio', 'active', 'tags'],
                Schema::properties => [
                    'name' => [Property::type => Property::string, Property::description => 'The display name'],
                    'count' => [Property::type => Property::integer],
                    'ratio' => [Property::type => Property::number],
                    'active' => [Property::type => Property::boolean],
                    'tags' => [Property::type => Schema::array],
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
