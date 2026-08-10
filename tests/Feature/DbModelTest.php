<?php

use App\Sources\Db\App\App;
use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Column;
use App\Sources\Db\Support\ColumnDefinition;
use App\Sources\Db\Support\ColumnType;
use App\Sources\Db\Support\DatabaseSchema;
use App\Sources\Db\Support\SourceSchema;
use App\Sources\Db\Support\TableDefinition;
use App\Sources\Db\Support\TableRenderer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('the database schema reads a table column by column', function (): void {
    $Users = DatabaseSchema::read()['users'];

    expect($Users->collate)->toBe(Collation::utf8mb4_unicode_ci->value)
        ->and($Users->indexes)->toBeEmpty()
        ->and(array_keys($Users->columns))->toBe([
            'id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at',
        ])
        ->and($Users->columns['id']->toArray())->toBe([
            Column::type => ColumnType::char->value,
            Column::length => 26,
            Column::primary_key => true,
        ])
        ->and($Users->columns['email']->toArray())->toBe([
            Column::type => ColumnType::varchar->value,
            Column::length => 255,
            Column::unique => true,
        ])
        ->and($Users->columns['email_verified_at']->toArray())->toBe([
            Column::type => ColumnType::timestamp->value,
            Column::nullable => true,
        ]);
});

test('the database schema keeps only the indexes a column cannot carry', function (): void {
    $tables = DatabaseSchema::read();

    expect($tables['personal_access_tokens']->indexes)->toBe([
        'personal_access_tokens_tokenable_id_tokenable_type_index' => ['tokenable_id', 'tokenable_type'],
    ])
        ->and($tables['personal_access_tokens']->columns['id']->toArray())->toBe([
            Column::type => ColumnType::bigint->value,
            Column::primary_key => true,
            Column::auto_increment => true,
        ])
        ->and($tables['sessions']->indexes)->toBe([
            'sessions_last_activity_index' => ['last_activity'],
            'sessions_user_id_index' => ['user_id'],
        ]);
});

test('the source schema resolves its enum, namespace and directory', function (): void {
    $SourceSchema = SourceSchema::make('App');

    expect($SourceSchema->schema)->toBe(App::class)
        ->and($SourceSchema->namespace)->toBe('App\Sources\Db\App')
        ->and($SourceSchema->directory)->toBe(app_path('Sources/Db/App'))
        ->and($SourceSchema->className('personal_access_tokens'))->toBe('PersonalAccessTokens')
        ->and($SourceSchema->path('users'))->toBe(app_path('Sources/Db/App/Users.php'));
});

test('the source schema reads every declared table and skips the schema enum', function (): void {
    $tables = SourceSchema::make('App')->tables();

    expect(array_keys($tables))->toBe(['cache', 'personal_access_tokens', 'users'])
        ->and($tables['personal_access_tokens']->indexes)->toBe([
            'personal_access_tokens_tokenable_id_tokenable_type_index' => ['tokenable_id', 'tokenable_type'],
        ])
        ->and($tables['users']->columns['email']->comment)->toBe('The users email');
});

test('the source schema rejects a directory without a schema enum', function (): void {
    expect(static fn (): SourceSchema => SourceSchema::make('Support'))
        ->toThrow(RuntimeException::class, 'No enum carrying the #[Schema] attribute was found');
});

test('the renderer reproduces a committed table enum byte for byte', function (): void {
    $rendered = new TableRenderer(SourceSchema::make('App'))
        ->render(DatabaseSchema::read()['personal_access_tokens']);

    expect($rendered)->toBe(File::get(app_path('Sources/Db/App/PersonalAccessTokens.php')));
});

test('the renderer writes a comment and omits an empty index list', function (): void {
    $rendered = new TableRenderer(SourceSchema::make('App'))->render(
        new TableDefinition('widgets', Collation::utf8mb4_unicode_ci->value, [
            'label' => new ColumnDefinition(
                name: 'label',
                type: ColumnType::varchar->value,
                length: 32,
                comment: "the widget's label",
                nullable: true,
            ),
        ]),
    );

    expect($rendered)->toContain("Column::comment => 'the widget\\'s label',")
        ->and($rendered)->toContain('enum Widgets: string')
        ->and($rendered)->not->toContain('Table::indexes');
});

test('the renderer rejects a collation it cannot name', function (): void {
    $TableRenderer = new TableRenderer(SourceSchema::make('App'));

    expect(static fn (): string => $TableRenderer->render(new TableDefinition('widgets', 'latin1_swedish_ci')))
        ->toThrow(RuntimeException::class, 'Unsupported collation [latin1_swedish_ci]');
});

test('the renderer rejects a column type it cannot name', function (): void {
    $TableRenderer = new TableRenderer(SourceSchema::make('App'));
    $TableDefinition = new TableDefinition('widgets', Collation::utf8mb4_unicode_ci->value, [
        'shape' => new ColumnDefinition(name: 'shape', type: 'geometry'),
    ]);

    expect(static fn (): string => $TableRenderer->render($TableDefinition))
        ->toThrow(RuntimeException::class, 'Unsupported column type [geometry]');
});

test('db-model:check fails while a table is missing from php', function (): void {
    expect(Artisan::call('db-model:check'))->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('Table [sessions] is not declared in PHP');
});

test('db-model:generate creates, leaves and rewrites the table enums', function (): void {
    $path = storage_path('framework/testing/db-model');

    File::deleteDirectory($path);

    expect(Artisan::call('db-model:generate', ['--path' => $path]))->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('created')
        ->and(File::get($path.'/PersonalAccessTokens.php'))->toBe(File::get(app_path('Sources/Db/App/PersonalAccessTokens.php')))
        ->and(Artisan::call('db-model:generate', ['--path' => $path]))->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('unchanged');

    File::put($path.'/Users.php', 'stale');

    expect(Artisan::call('db-model:generate', ['--path' => $path]))->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('updated')
        ->and(File::get($path.'/Users.php'))->not->toBe('stale');

    File::deleteDirectory($path);
});

test('db-model:generate writes nothing on a dry run', function (): void {
    expect(Artisan::call('db-model:generate', ['--dry-run' => true]))->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('Nothing was written.')
        ->and(File::exists(app_path('Sources/Db/App/Sessions.php')))->toBeFalse();
});
