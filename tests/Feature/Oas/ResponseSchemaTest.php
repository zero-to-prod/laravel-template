<?php

use App\Modules\Api\Models\ApiTokenResponse;
use App\Modules\Api\Models\Authorized;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ResponseSchema;
use Tests\Fixtures\OasResponseStub;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('the envelope carries the model as data', function (): void {
    expect(ResponseSchema::ok(ApiTokenResponse::class))->toBe([
        Schema::type => Schema::object,
        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::data, ApiResponse::type],
        Schema::properties => [
            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
            ApiResponse::message => [Property::type => Property::string],
            ApiResponse::data => [
                Schema::type => Schema::object,
                Schema::required => [ApiTokenResponse::token],
                Schema::properties => [
                    ApiTokenResponse::token => [
                        Property::type => Property::string,
                        Property::description => 'API authentication token',
                    ],
                ],
            ],
            ApiResponse::type => [
                Property::type => Property::string,
                Property::enum => [class_basename(ApiTokenResponse::class)],
            ],
        ],
    ]);
});

test('a model with no properties contributes no data key', function (): void {
    // Api::respond() strips the empty array, so publishing `data` would
    // describe a key the response never carries.
    expect(ResponseSchema::ok(Authorized::class))->toBe([
        Schema::type => Schema::object,
        Schema::required => [ApiResponse::success, ApiResponse::message, ApiResponse::type],
        Schema::properties => [
            ApiResponse::success => [Property::type => Property::boolean, Property::enum => [true]],
            ApiResponse::message => [Property::type => Property::string],
            ApiResponse::type => [Property::type => Property::string, Property::enum => ['Authorized']],
        ],
    ]);
});

test('property types map to their openapi equivalents, nullables are optional, and type tracks the basename', function (): void {
    // The `type` enum has to stay whatever Api::resolveType() would publish,
    // which is the payload's class basename.
    expect(ResponseSchema::ok(OasResponseStub::class))->toBe([
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
                    'nickname' => [Property::type => Property::string],
                ],
            ],
            ApiResponse::type => [
                Property::type => Property::string,
                Property::enum => [class_basename(OasResponseStub::class)],
            ],
        ],
    ]);
});
