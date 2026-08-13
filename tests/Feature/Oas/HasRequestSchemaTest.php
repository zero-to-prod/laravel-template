<?php

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tests\Fixtures\OasRequestStub;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**
 * A payload the declared schema accepts, so a single field carries the failure.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function oasRequest(array $overrides = []): array
{
    return [
        OasRequestStub::email => 'free@example.com',
        OasRequestStub::password => 'secret',
        OasRequestStub::password_confirmation => 'secret',
        OasRequestStub::nickname => 'nick',
        OasRequestStub::broken => 'x',
        ...$overrides,
    ];
}

test('a non scalar value is a 422 rather than a hydration TypeError', function (): void {
    // Validating the raw input keeps hydration off any payload the schema
    // rejects, so an array cannot reach a property typed as a string.
    $errors = OasRequestStub::validator(oasRequest([OasRequestStub::email => ['x']]))->errors();

    expect($errors->keys())->toBe([OasRequestStub::email])
        ->and($errors->first(OasRequestStub::email))->toBe('The email field must be a string.');
});

test('a value the document does not allow is rejected rather than coerced', function (): void {
    // The cast would have made this "123" and let it pass a `type: string`
    // schema, leaving the runtime laxer than the published document.
    $errors = OasRequestStub::validator(oasRequest([OasRequestStub::email => 123]))->errors();

    expect($errors->keys())->toBe([OasRequestStub::email]);
});

test('a blank required field conforms to the document but is still rejected', function (): void {
    // A required, non-nullable string translates to `required`, which rejects
    // "" without the document having to publish minLength: 1. That keeps the
    // 422 reachable by a request the document accepts.
    $errors = OasRequestStub::validator(oasRequest([OasRequestStub::email => '']))->errors();

    expect($errors->keys())->toBe([OasRequestStub::email])
        ->and($errors->first(OasRequestStub::email))->toBe('The email field is required.');
});

test('validate throws with the messages attached', function (): void {
    OasRequestStub::validator(oasRequest([OasRequestStub::email => 123]))->validate();
})->throws(ValidationException::class);

test('a closure description overrides the fragment, and a non array schema is dropped', function (): void {
    expect(OasRequestStub::schema()[Schema::properties] ?? [])->toBe([
        OasRequestStub::email => [Property::type => Property::string, Property::minLength => 1],
        OasRequestStub::password => [Property::type => Property::string],
        OasRequestStub::nickname => [
            Property::type => Property::string,
            Property::description => 'The users email',
        ],
        OasRequestStub::expires_at => [
            Property::type => Property::string,
            Property::format => Property::date_time,
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

    $errors = OasRequestStub::validator(oasRequest([
        OasRequestStub::email => 'taken@example.com',
        OasRequestStub::password_confirmation => 'mismatch',
    ]))->errors();

    expect($errors->keys())->toBe([OasRequestStub::email, OasRequestStub::password])
        ->and($errors->first(OasRequestStub::email))->toBe('That email is already taken.')
        ->and($errors->first(OasRequestStub::password))->toBe('The confirmation does not match.');
});

test('value checks are skipped when the schema already failed', function (): void {
    User::factory()->createOne(['email' => 'taken@example.com']);

    $errors = OasRequestStub::validator(oasRequest([OasRequestStub::email => '']))->errors();

    expect($errors->keys())->toBe([OasRequestStub::email])
        ->and($errors->first(OasRequestStub::email))->toBe('The email field is required.');
});

test('a passing value check adds nothing', function (): void {
    expect(OasRequestStub::validator(oasRequest())->passes())->toBeTrue();
});

test('an instant that has already passed is refused, however well formed it is', function (): void {
    $errors = OasRequestStub::validator(oasRequest([
        OasRequestStub::expires_at => now()->subDay()->toIso8601String(),
    ]))->errors();

    expect($errors->keys())->toBe([OasRequestStub::expires_at])
        ->and($errors->first(OasRequestStub::expires_at))
        ->toBe('The expires_at field must be a future date.');
});

test('an instant still ahead of now is accepted', function (): void {
    expect(OasRequestStub::validator(oasRequest([
        OasRequestStub::expires_at => now()->addDay()->toIso8601String(),
    ]))->passes())->toBeTrue();
});
