<?php

use App\Helpers\FieldViewDefaults;
use Tests\Fixtures\FieldStub;

test('bag is derived from the model class and falls back to default', function (): void {
    expect(FieldViewDefaults::bag(FieldStub::make()))->toBe('field_stub')
        ->and(FieldViewDefaults::bag(null))->toBe('default');
});

test('legend requires both a model and a name', function (): void {
    expect(FieldViewDefaults::legend(FieldStub::make(), FieldStub::website))->toBe('Website')
        ->and(FieldViewDefaults::legend(FieldStub::make(), null))->toBeNull()
        ->and(FieldViewDefaults::legend(null, FieldStub::website))->toBeNull();
});

test('placeholder requires a model', function (): void {
    expect(FieldViewDefaults::placeholder(FieldStub::make(), FieldStub::website))->toBe('https://example.com')
        ->and(FieldViewDefaults::placeholder(null, FieldStub::website))->toBeNull();
});

test('description requires both a model and a name', function (): void {
    expect(FieldViewDefaults::description(FieldStub::make(), FieldStub::website))->toBe('Homepage')
        ->and(FieldViewDefaults::description(FieldStub::make(), null))->toBeNull()
        ->and(FieldViewDefaults::description(null, FieldStub::website))->toBeNull();
});

test('value is null without a model and never echoes a sensitive field', function (): void {
    expect(FieldViewDefaults::value(null, FieldStub::website))->toBeNull()
        ->and(FieldViewDefaults::value(FieldStub::make(), FieldStub::secret))->toBeNull();
});

test('value falls back to the model property, or null for a class string model', function (): void {
    expect(FieldViewDefaults::value(FieldStub::make(), FieldStub::website))->toBe('https://example.com')
        ->and(FieldViewDefaults::value(FieldStub::class, FieldStub::website))->toBeNull();
});
