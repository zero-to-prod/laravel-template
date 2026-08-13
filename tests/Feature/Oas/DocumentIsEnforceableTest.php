<?php

use App\Modules\Api\Support\ApiResponse;
use PHPUnit\Framework\AssertionFailedError;
use Tests\Support\OasDocument;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;
use ZeroToProd\SchemaValidator\SchemaValidator;
use ZeroToProd\SchemaValidator\UnsupportedKeyword;

// Layer 1: keyword and format expressibility. `make()` builds the rules eagerly,
// so an empty payload is enough to ask whether the request validator can express
// what the document says, without asserting anything about a value.
test('every body the document publishes can be expressed as validation rules', function (): void {
    $unenforceable = [];

    foreach (OasDocument::generated()->bodySchemas() as $operation => $schema) {
        try {
            SchemaValidator::make([], $schema);
        } catch (UnsupportedKeyword $UnsupportedKeyword) {
            $unenforceable[] = $operation.': '.$UnsupportedKeyword->getMessage();
        }
    }

    expect($unenforceable)->toBeEmpty(
        "The document publishes what the request validator cannot express:\n  - ".implode("\n  - ", $unenforceable)
    );
});

test('the walk reaches every request and response body, an empty one passing vacuously', function (): void {
    $operations = array_keys(OasDocument::generated()->bodySchemas());

    expect($operations)->toContain('post /api/login request')
        ->toContain('get /api/user response 200')
        // The error envelopes are published as `$ref`, so reaching these is what
        // says the references were resolved rather than skipped.
        ->toContain('post /api/login response 401')
        ->toContain('post /api/login response 422');
});

test('a schema the request validator cannot express is reported rather than passed over', function (): void {
    // Pinned against a synthetic schema so that proving the guard works never
    // means publishing a keyword a real endpoint does not use.
    SchemaValidator::make([], [
        Schema::type => Schema::object,
        Schema::properties => ['a' => ['allOf' => [[Property::type => Property::string]]]],
    ]);
})->throws(UnsupportedKeyword::class, 'Unsupported OpenAPI keyword `allOf` at `a`.');

// Layer 2: value level agreement.
test('a body the document admits under league and refuses under the request rules is reported', function (): void {
    // What `date-time` was: league admits RFC 3339, and a mapping of
    // `date_format:Y-m-d\TH:i:sP` did not, so the API documented and emitted a
    // timestamp its own request validator would have rejected. A space for the
    // separator is a value league accepts as a `date-time` to this day.
    $this->assertBodyMatchesRules(
        [
            Schema::type => Schema::object,
            Schema::required => [ApiResponse::data],
            Schema::properties => [
                ApiResponse::data => [Property::type => Property::string, Property::format => Property::date_time],
            ],
        ],
        [ApiResponse::data => '2026-08-10 12:00:00'],
        'GET /synthetic 200',
    );
})->throws(AssertionFailedError::class, 'refuses it under the request validator');

test('a conforming body is admitted by both, the cross check adding no failure of its own', function (): void {
    $this->assertBodyMatchesRules(
        [
            Schema::type => Schema::object,
            Schema::required => [ApiResponse::data],
            Schema::properties => [
                ApiResponse::data => [Property::type => Property::string, Property::format => Property::date_time],
            ],
        ],
        // What Model::serializeDate() publishes, which is the value the defect
        // was found on.
        [ApiResponse::data => '2026-08-10T12:00:00.000000Z'],
        'GET /synthetic 200',
    );
});
