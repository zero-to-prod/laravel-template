<?php

use App\Models\User;
use App\Modules\Api\Requests\ApiLoginRequest;
use Illuminate\Validation\ValidationException;
use Tests\Fixtures\OasRequestStub;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('the request body schema is assembled from the property attributes', function (): void {
    expect(ApiLoginRequest::rules())->toBe([
        Schema::type => Schema::object,
        Schema::required => [
            ApiLoginRequest::email,
            ApiLoginRequest::password,
            ApiLoginRequest::device_name,
        ],
        Schema::properties => [
            ApiLoginRequest::email => [
                Property::type => Property::string,
                Property::maxLength => 255,
                Property::description => 'The users email',
                Property::format => Property::email,
            ],
            ApiLoginRequest::password => [
                Property::type => Property::string,
                Property::maxLength => 255,
                Property::description => 'User password',
            ],
            ApiLoginRequest::device_name => [
                Property::type => Property::string,
                Property::maxLength => 255,
                Property::description => 'Name of the requesting device',
            ],
        ],
    ]);
});

test('a conforming request validates', function (): void {
    $ApiLoginRequest = ApiLoginRequest::from([
        ApiLoginRequest::email => 'user@example.com',
        ApiLoginRequest::password => 'secret',
        ApiLoginRequest::device_name => 'phone',
    ]);

    expect($ApiLoginRequest->validator()->passes())->toBeTrue();
});

test('a blank password conforms to the document but is still rejected', function (): void {
    // A required, non-nullable string translates to `required`, which rejects
    // "" without the document having to publish minLength: 1. That keeps the
    // 422 reachable by a request the document accepts.
    $errors = ApiLoginRequest::from([
        ApiLoginRequest::email => 'user@example.com',
        ApiLoginRequest::password => '',
        ApiLoginRequest::device_name => 'phone',
    ])->validator()->errors();

    expect($errors->keys())->toBe([ApiLoginRequest::password])
        ->and($errors->first(ApiLoginRequest::password))->toBe('The password field is required.');
});

test('validate throws with the messages attached', function (): void {
    ApiLoginRequest::from([
        ApiLoginRequest::email => 'nope',
        ApiLoginRequest::password => 'secret',
        ApiLoginRequest::device_name => 'phone',
    ])->validator()->validate();
})->throws(ValidationException::class);

test('a closure description overrides the fragment, and a non array schema is dropped', function (): void {
    expect(OasRequestStub::rules()[Schema::properties] ?? [])->toBe([
        OasRequestStub::email => [Property::type => Property::string, Property::minLength => 1],
        OasRequestStub::password => [Property::type => Property::string],
        OasRequestStub::nickname => [
            Property::type => Property::string,
            Property::description => 'The users email',
        ],
        OasRequestStub::broken => [],
    ]);
});

test('only properties flagged required are hoisted', function (): void {
    expect(OasRequestStub::rules()[Schema::required] ?? [])
        ->toBe([OasRequestStub::email, OasRequestStub::password]);
});

test('value checks run once the schema passes', function (): void {
    User::factory()->createOne(['email' => 'taken@example.com']);

    $errors = OasRequestStub::from([
        OasRequestStub::email => 'taken@example.com',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'mismatch',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
    ])->validator()->errors();

    expect($errors->keys())->toBe([OasRequestStub::email, OasRequestStub::password])
        ->and($errors->first(OasRequestStub::email))->toBe('That email is already taken.')
        ->and($errors->first(OasRequestStub::password))->toBe('The confirmation does not match.');
});

test('value checks are skipped when the schema already failed', function (): void {
    User::factory()->createOne(['email' => 'taken@example.com']);

    $errors = OasRequestStub::from([
        OasRequestStub::email => '',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'secret',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
    ])->validator()->errors();

    expect($errors->keys())->toBe([OasRequestStub::email])
        ->and($errors->first(OasRequestStub::email))->toBe('The email field is required.');
});

test('a passing value check adds nothing', function (): void {
    $Validator = OasRequestStub::from([
        OasRequestStub::email => 'free@example.com',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'secret',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
    ])->validator();

    expect($Validator->passes())->toBeTrue();
});
