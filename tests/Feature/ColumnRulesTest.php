<?php

use App\Sources\Db\App\Users;
use App\Sources\Db\Support\ColumnType;

test('a column becomes a list of validation rules', function (): void {
    expect(Users::email->rules())->toBe(['required', 'string', 'max:255']);
});

test('a nullable column is nullable rather than required', function (): void {
    expect(Users::email_verified_at->rules())->toBe(['nullable', 'date']);
});

test('a length is only applied to a string rule', function (): void {
    expect(Users::id->rules())->toBe(['required', 'string', 'max:26'])
        ->and(Users::created_at->rules())->toBe(['nullable', 'date']);
});

test('unique is not emitted, it is declared per request', function (): void {
    expect(Users::email->rules())->not->toContain('unique');
});

test('each column type declares a validation rule', function (): void {
    expect(ColumnType::varchar->rule())->toBe('string')
        ->and(ColumnType::mediumtext->rule())->toBe('string')
        ->and(ColumnType::char->rule())->toBe('string')
        ->and(ColumnType::int->rule())->toBe('integer')
        ->and(ColumnType::timestamp->rule())->toBe('date');
});
