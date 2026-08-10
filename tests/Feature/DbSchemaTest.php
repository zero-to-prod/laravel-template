<?php

use App\Sources\Db\App\App;
use App\Sources\Db\App\Cache;
use App\Sources\Db\App\PersonalAccessTokens;
use App\Sources\Db\App\Users;
use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Column;
use App\Sources\Db\Support\Schema;
use App\Sources\Db\Support\Table;

test('the app schema declares its name and collation', function (): void {
    $Schema = (new ReflectionClass(App::class))->getAttributes(Schema::class)[0]->newInstance();

    expect($Schema->attributes)->toBe([
        Schema::name => 'app',
        Schema::collate => Collation::utf8mb4_0900_ai_ci->value,
    ]);
});

test('the cache table declares its schema, name and collation', function (): void {
    $Table = (new ReflectionClass(Cache::class))->getAttributes(Table::class)[0]->newInstance();

    expect($Table->schema)->toBe(App::class)
        ->and($Table->attributes)->toBe([
            Table::name => 'cache',
            Table::collate => Collation::utf8mb4_unicode_ci->value,
        ]);
});

/**
 * @param  class-string  $class
 * @return array<string, array<string, mixed>>
 */
function columns(string $class): array
{
    $columns = [];

    // Tables are enums, whose cases are class constants and so are invisible
    // to getProperties().
    foreach (new ReflectionClass($class)->getReflectionConstants() as $Constant) {
        foreach ($Constant->getAttributes(Column::class) as $Attribute) {
            $columns[$Constant->getName()] = $Attribute->newInstance()->attributes;
        }
    }

    return $columns;
}

test('the cache table declares a column per case', function (): void {
    expect(columns(Cache::class))->toBe([
        Cache::key->value => [
            Column::name => Cache::key,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
            Column::primary_key => true,
        ],
        Cache::value->value => [
            Column::name => Cache::value,
            Column::type => 'mediumtext',
            Column::nullable => false,
        ],
        Cache::expiration->value => [
            Column::name => Cache::expiration,
            Column::type => 'int',
            Column::nullable => false,
        ],
    ]);
});

test('the users table declares its schema, name and collation', function (): void {
    $Table = (new ReflectionClass(Users::class))->getAttributes(Table::class)[0]->newInstance();

    expect($Table->schema)->toBe(App::class)
        ->and($Table->attributes)->toBe([
            Table::name => 'users',
            Table::collate => Collation::utf8mb4_unicode_ci->value,
        ]);
});

test('the users table declares a column per case', function (): void {
    expect(columns(Users::class))->toBe([
        Users::id->value => [
            Column::name => Users::id,
            Column::type => 'char',
            Column::length => 26,
            Column::nullable => false,
            Column::primary_key => true,
        ],
        Users::name->value => [
            Column::name => Users::name,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
        ],
        Users::email->value => [
            Column::name => Users::email,
            Column::comment => 'The users email',
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
            Column::unique => true,
        ],
        Users::email_verified_at->value => [
            Column::name => Users::email_verified_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
        Users::password->value => [
            Column::name => Users::password,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
        ],
        Users::remember_token->value => [
            Column::name => Users::remember_token,
            Column::type => 'varchar',
            Column::length => 100,
            Column::nullable => true,
        ],
        Users::created_at->value => [
            Column::name => Users::created_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
        Users::updated_at->value => [
            Column::name => Users::updated_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
    ]);
});

test('the personal access tokens table declares its schema, name, collation and indexes', function (): void {
    $Table = (new ReflectionClass(PersonalAccessTokens::class))->getAttributes(Table::class)[0]->newInstance();

    expect($Table->schema)->toBe(App::class)
        ->and($Table->attributes)->toBe([
            Table::name => 'personal_access_tokens',
            Table::collate => Collation::utf8mb4_unicode_ci->value,
            Table::indexes => [
                'personal_access_tokens_tokenable_id_tokenable_type_index' => [
                    PersonalAccessTokens::tokenable_id,
                    PersonalAccessTokens::tokenable_type,
                ],
            ],
        ]);
});

test('a table without a composite index declares none', function (): void {
    $Table = (new ReflectionClass(Users::class))->getAttributes(Table::class)[0]->newInstance();

    expect($Table->attributes)->not->toHaveKey(Table::indexes);
});

test('the personal access tokens table declares a column per case', function (): void {
    expect(columns(PersonalAccessTokens::class))->toBe([
        PersonalAccessTokens::id->value => [
            Column::name => PersonalAccessTokens::id,
            Column::type => 'bigint',
            Column::nullable => false,
            Column::primary_key => true,
            Column::auto_increment => true,
        ],
        PersonalAccessTokens::tokenable_type->value => [
            Column::name => PersonalAccessTokens::tokenable_type,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
        ],
        PersonalAccessTokens::tokenable_id->value => [
            Column::name => PersonalAccessTokens::tokenable_id,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
        ],
        PersonalAccessTokens::name->value => [
            Column::name => PersonalAccessTokens::name,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
        ],
        PersonalAccessTokens::token->value => [
            Column::name => PersonalAccessTokens::token,
            Column::type => 'varchar',
            Column::length => 64,
            Column::nullable => false,
            Column::unique => true,
        ],
        PersonalAccessTokens::abilities->value => [
            Column::name => PersonalAccessTokens::abilities,
            Column::type => 'text',
            Column::nullable => true,
        ],
        PersonalAccessTokens::last_used_at->value => [
            Column::name => PersonalAccessTokens::last_used_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
        PersonalAccessTokens::expires_at->value => [
            Column::name => PersonalAccessTokens::expires_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
        PersonalAccessTokens::created_at->value => [
            Column::name => PersonalAccessTokens::created_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
        PersonalAccessTokens::updated_at->value => [
            Column::name => PersonalAccessTokens::updated_at,
            Column::type => 'timestamp',
            Column::nullable => true,
        ],
    ]);
});
