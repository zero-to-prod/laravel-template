<?php

use App\Helpers\Rule;

test('max renders a bounded rule', function (): void {
    expect(Rule::max(255))->toBe('max:255');
});

test('min renders a bounded rule', function (): void {
    expect(Rule::min(8))->toBe('min:8');
});

test('required if renders the field and its triggering values', function (): void {
    expect(Rule::requiredIf('type', 'a', 'b'))->toBe('required_if:type,a,b');
});

test('regex renders the pattern', function (): void {
    expect(Rule::regex('/^a$/'))->toBe('regex:/^a$/');
});

test('in renders a comma separated list', function (): void {
    expect(Rule::in('a', 'b'))->toBe('in:a,b');
});

test('email renders its validations', function (): void {
    expect(Rule::email('rfc', 'dns'))->toBe('email:rfc,dns');
});

test('unique renders the table and optionally the column', function (): void {
    expect(Rule::unique('users'))->toBe('unique:users')
        ->and(Rule::unique('users', 'email'))->toBe('unique:users,email');
});

test('exists renders the table and optionally the column', function (): void {
    expect(Rule::exists('users'))->toBe('exists:users')
        ->and(Rule::exists('users', 'email'))->toBe('exists:users,email');
});
