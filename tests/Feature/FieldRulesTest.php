<?php

use App\DataModels\Fields\GenericEmail;
use App\DataModels\Fields\GenericString;
use App\DataModels\User;
use App\Helpers\Rule;

test('a generic string is a bounded required string', function (): void {
    expect(GenericString::rules())->toBe([
        Rule::required,
        Rule::string,
        Rule::max(GenericString::length),
    ]);
});

test('a generic email is a bounded required email', function (): void {
    expect(GenericEmail::rules())->toBe([
        Rule::required,
        Rule::string,
        Rule::email,
        Rule::max(GenericEmail::length),
    ]);
});

test('a password is a bounded required string', function (): void {
    expect(User::passwordRules())->toBe([
        Rule::required,
        Rule::string,
        Rule::max(255),
    ]);
});

test('a mailbox id is a bounded optional string', function (): void {
    expect(User::mailboxIdRules())->toBe([
        Rule::nullable,
        Rule::string,
        Rule::max(255),
    ]);
});
