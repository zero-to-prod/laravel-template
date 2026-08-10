<?php

use App\Sources\Db\App\PersonalAccessTokens;
use App\Sources\Db\App\Users;
use App\Sources\Db\Support\ColumnType;
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
        Property::nullable => true,
    ]);
});

test('unique is not published as a schema keyword', function (): void {
    expect(Users::email->schema())->not->toHaveKey('unique');
});

test('auto increment is not published as a schema keyword', function (): void {
    expect(PersonalAccessTokens::id->schema())->toBe([Property::type => Property::integer])
        ->and(PersonalAccessTokens::id->auto_increment())->toBeTrue();
});

test('an absent column attribute reads as null rather than throwing', function (): void {
    expect(Users::name->comment())->toBeNull()
        ->and(Users::name->attribute('nonexistent'))->toBeNull();
});

test('a column attribute is readable by name', function (): void {
    expect(Users::email->length())->toBe(255)
        ->and(Users::email->unique())->toBeTrue();
});

test('each column type declares an openapi type', function (): void {
    expect(ColumnType::varchar->oas())->toBe([Property::type => Property::string])
        ->and(ColumnType::mediumtext->oas())->toBe([Property::type => Property::string])
        ->and(ColumnType::text->oas())->toBe([Property::type => Property::string])
        ->and(ColumnType::char->oas())->toBe([Property::type => Property::string])
        ->and(ColumnType::int->oas())->toBe([Property::type => Property::integer])
        ->and(ColumnType::bigint->oas())->toBe([Property::type => Property::integer])
        ->and(ColumnType::timestamp->oas())
        ->toBe([Property::type => Property::string, Property::format => Property::date_time]);
});
