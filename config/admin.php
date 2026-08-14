<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | The First Admin
    |--------------------------------------------------------------------------
    |
    | The account the admin-role migration grants the role to. `email` and
    | `password` are both required: with either unset the migration creates the
    | role and no user, which is what you want anywhere the credentials are not
    | supposed to live in the environment.
    |
    | The password is hashed when the row is written, so rotating it means
    | changing ADMIN_PASSWORD and re-running the migration.
    |
    */

    'name' => env('ADMIN_NAME', 'Admin'),

    'email' => env('ADMIN_EMAIL', 'admin@example.com'),

    'password' => env('ADMIN_PASSWORD', 'password'),

];
