<?php

use App\Helpers\DataModelCast;

test('sanitize squishes whitespace and coerces null to a string', function (): void {

    expect(DataModelCast::sanitize("  a   b \n"))->toBe('a b')
        ->and(DataModelCast::sanitize(null))->toBeEmpty();
});

test('sanitize nullable returns null for an empty value', function (): void {
    expect(DataModelCast::sanitizeNullable('  a   b  '))->toBe('a b')
        ->and(DataModelCast::sanitizeNullable('   '))->toBeNull()
        ->and(DataModelCast::sanitizeNullable(null))->toBeNull();
});

test('sanitize email squishes and lowercases', function (): void {
    expect(DataModelCast::sanitizeEmail('  JOHN@Example.COM '))->toBe('john@example.com')
        ->and(DataModelCast::sanitizeEmail(null))->toBeEmpty();
});

test('to int nullable returns null for null and empty string', function (): void {
    expect(DataModelCast::toIntNullable('5'))->toBe(5)
        ->and(DataModelCast::toIntNullable(null))->toBeNull()
        ->and(DataModelCast::toIntNullable(''))->toBeNull();
});
