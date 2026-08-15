<?php

declare(strict_types=1);

use App\Helpers\Role;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\PublicApiSchema;
use App\Routes\Admin;
use App\Routes\MiddlewareTag;

return [
    // The package's single-schema route is replaced by the schema routes below.
    'route' => [
        'enabled' => false,
    ],

    'schemas' => [
        'public' => [
            'attribute' => PublicApiSchema::class,
            'route' => [
                'uri' => 'openapi.json',
                'name' => 'openapi.public',
                'middleware' => [MiddlewareTag::api->value],
            ],
            'openapi' => [
                'openapi' => '3.0.4',
                'info' => [
                    'title' => env('APP_NAME', 'Laravel').' API',
                    'version' => '1.0.0',
                ],
                'servers' => [['url' => '/']],
            ],
        ],
        'admin' => [
            'attribute' => AdminApiSchema::class,
            'route' => [
                'uri' => ltrim(Admin::openapi->value, '/'),
                'name' => 'openapi.admin',
                'middleware' => [
                    MiddlewareTag::web->value,
                    MiddlewareTag::auth->value,
                    Role::admin->middleware(),
                ],
            ],
            'openapi' => [
                'openapi' => '3.0.4',
                'info' => [
                    'title' => env('APP_NAME', 'Laravel').' Admin API',
                    'version' => '1.0.0',
                ],
                'servers' => [['url' => '/']],
            ],
        ],
    ],

    'validation' => ['declared_paths' => true],
    'mcp' => ['enabled' => true, 'handle' => 'laravel-openapi'],
    'coverage' => ['path' => storage_path('framework/cache/openapi-coverage.jsonl')],
];
