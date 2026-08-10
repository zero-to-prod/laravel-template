<?php

use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Column;
use App\Sources\Db\Support\ColumnDefinition;
use App\Sources\Db\Support\ColumnType;
use App\Sources\Db\Support\SchemaDiff;
use App\Sources\Db\Support\Table;
use App\Sources\Db\Support\TableDefinition;

/**
 * @param  array<string, ColumnDefinition>  $columns
 * @param  array<string, list<string>>  $indexes
 */
function widgets(array $columns = [], string $collate = 'utf8mb4_unicode_ci', array $indexes = []): TableDefinition
{
    return new TableDefinition('widgets', $collate, $columns, $indexes);
}

function label(bool $nullable = false): ColumnDefinition
{
    return new ColumnDefinition(name: 'label', type: ColumnType::varchar->value, length: 32, nullable: $nullable);
}

test('an identical database and source produce no differences', function (): void {
    $tables = ['widgets' => widgets(['label' => label()])];

    expect(new SchemaDiff($tables, $tables)->differences())->toBeEmpty();
});

test('a table only in the database is reported as undeclared', function (): void {
    expect(new SchemaDiff(['widgets' => widgets()], [])->differences())
        ->toBe(['Table [widgets] is not declared in PHP.']);
});

test('a table only in php is reported as nonexistent', function (): void {
    expect(new SchemaDiff([], ['widgets' => widgets()])->differences())
        ->toBe(['Table [widgets] is declared in PHP but does not exist in the database.']);
});

test('a differing collation is reported', function (): void {
    $differences = new SchemaDiff(
        ['widgets' => widgets()],
        ['widgets' => widgets(collate: Collation::utf8mb4_0900_ai_ci->value)],
    )->differences();

    expect($differences)->toBe([
        'Table [widgets] declares collate [utf8mb4_0900_ai_ci], expected [utf8mb4_unicode_ci].',
    ]);
});

test('differing indexes are reported', function (): void {
    $differences = new SchemaDiff(
        ['widgets' => widgets(indexes: ['widgets_label_index' => ['label']])],
        ['widgets' => widgets()],
    )->differences();

    expect($differences)->toBe([
        'Table [widgets] declares indexes [], expected {"widgets_label_index":["label"]}.',
    ]);
});

test('a column only in the database is reported as undeclared', function (): void {
    expect(new SchemaDiff(['widgets' => widgets(['label' => label()])], ['widgets' => widgets()])->differences())
        ->toBe(['Column [widgets.label] is not declared in PHP.']);
});

test('a column only in php is reported as nonexistent', function (): void {
    expect(new SchemaDiff(['widgets' => widgets()], ['widgets' => widgets(['label' => label()])])->differences())
        ->toBe(['Column [widgets.label] is declared in PHP but does not exist in the database.']);
});

test('a column whose attributes differ is reported', function (): void {
    $differences = new SchemaDiff(
        ['widgets' => widgets(['label' => label()])],
        ['widgets' => widgets(['label' => label(nullable: true)])],
    )->differences();

    expect($differences)->toBe([
        'Column [widgets.label] declares {"type":"varchar","length":32,"nullable":true}, expected {"type":"varchar","length":32}.',
    ]);
});

test('columns declared out of order are reported', function (): void {
    $id = new ColumnDefinition(name: 'id', type: ColumnType::int->value);

    $differences = new SchemaDiff(
        ['widgets' => widgets(['id' => $id, 'label' => label()])],
        ['widgets' => widgets(['label' => label(), 'id' => $id])],
    )->differences();

    expect($differences)->toBe([
        'Table [widgets] declares columns in the order ["label","id"], expected ["id","label"].',
    ]);
});

test('a column definition drops the attributes that carry no information', function (): void {
    expect(label()->toArray())->toBe([
        Column::type => 'varchar',
        Column::length => 32,
    ])->and(new ColumnDefinition(
        name: 'id',
        type: ColumnType::bigint->value,
        comment: 'the id',
        unique: true,
        primary_key: true,
        auto_increment: true,
    )->toArray())->toBe([
        Column::type => 'bigint',
        Column::comment => 'the id',
        Column::unique => true,
        Column::primary_key => true,
        Column::auto_increment => true,
    ]);
});

test('a definition built from attributes falls back to empty values', function (): void {
    $TableDefinition = TableDefinition::fromAttributes([Table::indexes => 'not an array'], [
        'label' => ColumnDefinition::fromAttributes([Column::name => 'not an enum case']),
    ]);

    expect($TableDefinition->name)->toBeEmpty()
        ->and($TableDefinition->collate)->toBeEmpty()
        ->and($TableDefinition->indexes)->toBeEmpty()
        ->and($TableDefinition->columns['label']->name)->toBeEmpty()
        ->and($TableDefinition->columns['label']->type)->toBeEmpty()
        ->and($TableDefinition->columns['label']->length)->toBeNull();
});
