<?php

use App\Sources\Db\App\Migrations;
use App\Sources\Db\App\PersonalAccessTokens;
use App\Sources\Db\App\Users;
use Tests\Fixtures\UntabledStub;
use ZeroToProd\SchemaValidator\Property;

test('a column becomes an openapi schema object', function (): void {
    expect(Users::email->schema())->toBe([
        Property::type => Property::string,
        Property::maxLength => 255,
        Property::description => 'The users email',
    ]);
});

test('a nullable timestamp carries its format and nullability', function (): void {
    expect(Users::email_verified_at->schema())->toBe([
        Property::type => Property::string,
        Property::format => Property::date_time,
        Property::description => 'When the users email was verified',
        Property::nullable => true,
    ]);
});

test('unique is not published as a schema keyword', function (): void {
    expect(Users::email->schema())->not->toHaveKey('unique');
});

test('auto increment is not published as a schema keyword', function (): void {
    expect(PersonalAccessTokens::id->schema())->toBe([
        Property::type => Property::integer,
        Property::description => 'The unique identifier of the token',
    ])->and(PersonalAccessTokens::id->auto_increment())->toBeTrue();
});

// The migrations table is created by the framework rather than by a migration
// of ours, so it is the one table whose columns carry no comment.
test('an absent column attribute reads as null rather than throwing', function (): void {
    expect(Migrations::batch->comment())->toBeNull()
        ->and(Migrations::batch->attribute('nonexistent'))->toBeNull();
});

test('a column attribute is readable by name', function (): void {
    expect(Users::email->length())->toBe(255)
        ->and(Users::email->unique())->toBeTrue();
});

test('the enum names the table it mirrors', function (): void {
    expect(Users::table())->toBe('users')
        ->and(PersonalAccessTokens::table())->toBe('personal_access_tokens');
});

test('an enum declaring no table name reads as empty rather than throwing', function (): void {
    expect(UntabledStub::table())->toBeEmpty();
});
