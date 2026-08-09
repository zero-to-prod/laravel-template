<?php

use App\Sources\Db\App\App;
use App\Sources\Db\App\Cache;
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
    $Reflection = new ReflectionClass($class);

    // Cache declares columns on properties; Users is an enum, whose cases are
    // class constants and so are invisible to getProperties().
    foreach ($Reflection->getProperties() as $Property) {
        foreach ($Property->getAttributes(Column::class) as $Attribute) {
            $columns[$Property->getName()] = $Attribute->newInstance()->attributes;
        }
    }

    foreach ($Reflection->getReflectionConstants() as $Constant) {
        foreach ($Constant->getAttributes(Column::class) as $Attribute) {
            $columns[$Constant->getName()] = $Attribute->newInstance()->attributes;
        }
    }

    return $columns;
}

test('the cache table declares a column per property', function (): void {
    expect(columns(Cache::class))->toBe([
        Cache::key => [
            Column::name => Cache::key,
            Column::type => 'varchar',
            Column::length => 255,
            Column::nullable => false,
            Column::primary_key => true,
        ],
        Cache::value => [
            Column::name => Cache::value,
            Column::type => 'mediumtext',
            Column::nullable => false,
        ],
        Cache::expiration => [
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

test('the users table declares a column per property', function (): void {
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
