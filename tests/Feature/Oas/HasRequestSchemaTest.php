<?php

use App\Models\User;
use App\Modules\Api\Login\LoginRequest;
use Illuminate\Validation\ValidationException;
use Tests\Fixtures\OasRequestStub;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('the request body schema is assembled from the property attributes', function (): void {
    expect(LoginRequest::schema())->toBe([
        Schema::type => Schema::object,
        Schema::required => [
            LoginRequest::email,
            LoginRequest::password,
            LoginRequest::device_name,
        ],
        Schema::properties => [
            LoginRequest::email => [
                Property::type => Property::string,
                Property::maxLength => 255,
                Property::description => 'User email',
                Property::format => Property::email,
            ],
            LoginRequest::password => [
                Property::type => Property::string,
                Property::maxLength => 255,
                Property::description => 'User password',
            ],
            LoginRequest::device_name => [
                Property::type => Property::string,
                Property::maxLength => 255,
                Property::description => 'Name of the requesting device',
            ],
        ],
    ]);
});

test('a conforming request validates', function (): void {
    expect(LoginRequest::validator([
        LoginRequest::email => 'user@example.com',
        LoginRequest::password => 'secret',
        LoginRequest::device_name => 'phone',
    ])->passes())->toBeTrue();
});

test('a non scalar value is a 422 rather than a hydration TypeError', function (): void {
    // Validating the raw input keeps `from()` off any payload the schema
    // rejects, so `password[]=x` cannot reach the typed property.
    $errors = LoginRequest::validator([
        LoginRequest::email => 'user@example.com',
        LoginRequest::password => ['x'],
        LoginRequest::device_name => 'phone',
    ])->errors();

    expect($errors->keys())->toBe([LoginRequest::password])
        ->and($errors->first(LoginRequest::password))->toBe('The password field must be a string.');
});

test('a value the document does not allow is rejected rather than coerced', function (): void {
    // The cast would have made this "123" and let it pass a `type: string`
    // schema, leaving the runtime laxer than the published document.
    $errors = LoginRequest::validator([
        LoginRequest::email => 'user@example.com',
        LoginRequest::password => 123,
        LoginRequest::device_name => 'phone',
    ])->errors();

    expect($errors->keys())->toBe([LoginRequest::password]);
});

test('a blank password conforms to the document but is still rejected', function (): void {
    // A required, non-nullable string translates to `required`, which rejects
    // "" without the document having to publish minLength: 1. That keeps the
    // 422 reachable by a request the document accepts.
    $errors = LoginRequest::validator([
        LoginRequest::email => 'user@example.com',
        LoginRequest::password => '',
        LoginRequest::device_name => 'phone',
    ])->errors();

    expect($errors->keys())->toBe([LoginRequest::password])
        ->and($errors->first(LoginRequest::password))->toBe('The password field is required.');
});

test('validate throws with the messages attached', function (): void {
    LoginRequest::validator([
        LoginRequest::email => 'nope',
        LoginRequest::password => 'secret',
        LoginRequest::device_name => 'phone',
    ])->validate();
})->throws(ValidationException::class);

test('a closure description overrides the fragment, and a non array schema is dropped', function (): void {
    expect(OasRequestStub::schema()[Schema::properties] ?? [])->toBe([
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
    expect(OasRequestStub::schema()[Schema::required] ?? [])
        ->toBe([OasRequestStub::email, OasRequestStub::password]);
});

test('value checks run once the schema passes', function (): void {
    User::factory()->createOne(['email' => 'taken@example.com']);

    $errors = OasRequestStub::validator([
        OasRequestStub::email => 'taken@example.com',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'mismatch',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
    ])->errors();

    expect($errors->keys())->toBe([OasRequestStub::email, OasRequestStub::password])
        ->and($errors->first(OasRequestStub::email))->toBe('That email is already taken.')
        ->and($errors->first(OasRequestStub::password))->toBe('The confirmation does not match.');
});

test('value checks are skipped when the schema already failed', function (): void {
    User::factory()->createOne(['email' => 'taken@example.com']);

    $errors = OasRequestStub::validator([
        OasRequestStub::email => '',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'secret',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
    ])->errors();

    expect($errors->keys())->toBe([OasRequestStub::email])
        ->and($errors->first(OasRequestStub::email))->toBe('The email field is required.');
});

test('a passing value check adds nothing', function (): void {
    $Validator = OasRequestStub::validator([
        OasRequestStub::email => 'free@example.com',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'secret',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
    ]);

    expect($Validator->passes())->toBeTrue();
});
