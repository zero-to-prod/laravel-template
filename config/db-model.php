<?php

declare(strict_types=1);

use App\Sources\Db\HasColumn;

return [

    /*
    |--------------------------------------------------------------------------
    | Source Artifacts
    |--------------------------------------------------------------------------
    |
    | Where the PHP table enums live. `namespace` and `path` must describe the
    | same place: a directory holding one subdirectory per schema, named after
    | it. `--schema=App` therefore resolves to:
    |
    |     App\Sources\Db\App\App   the enum carrying the #[Schema] attribute
    |     app/Sources/Db/App/*.php the enums carrying the #[Table] attribute
    |
    */

    'namespace' => 'App\\Sources\\Db',

    'path' => app_path('Sources/Db'),

    /*
    |--------------------------------------------------------------------------
    | Column Trait
    |--------------------------------------------------------------------------
    |
    | The trait every generated table enum uses. The default reads the
    | #[Column] attribute and nothing more: a column type reports the native
    | PHP type that carries it, and what that means to a validator or a schema
    | generator is yours to define.
    |
    | Point this at a trait of your own that uses HasColumnAttribute to add
    | those mappings, and the generator will use it instead.
    |
    */

    'trait' => HasColumn::class,

    /*
    |--------------------------------------------------------------------------
    | MCP Server
    |--------------------------------------------------------------------------
    |
    | The package registers an MCP server so coding agents can read how it is
    | meant to be used. It requires laravel/mcp, and is a no-op without it:
    |
    |     composer require --dev laravel/mcp
    |     php artisan mcp:start db-model
    |
    | The `handle` is the name the server is registered under, which is the
    | argument to `mcp:start` and the name your agent refers to it by.
    |
    */

    'mcp' => [
        'enabled' => true,
        'handle' => 'db-model',
    ],

];
