<?php

use App\Helpers\Rule;

test('max renders a bounded rule', function (): void {
    expect(Rule::max(255))->toBe('max:255');
});

test('unique renders the table and optionally the column', function (): void {
    expect(Rule::unique('users'))->toBe('unique:users')
        ->and(Rule::unique('users', 'email'))->toBe('unique:users,email');
});
