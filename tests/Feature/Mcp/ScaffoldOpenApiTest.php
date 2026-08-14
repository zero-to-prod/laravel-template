<?php

use App\Mcp\Servers\TemplateServer;
use App\Mcp\Tools\ScaffoldOpenApi;

test('it scaffolds the operations in an openapi 3 schema', function (): void {
    $schema = [
        'openapi' => '3.1.0',
        'info' => ['title' => 'Widgets', 'version' => '1.0.0'],
        'paths' => [
            '/api/widgets/{widget}' => [
                'get' => [
                    'operationId' => 'showWidget',
                    'summary' => 'Show a widget.',
                    'tags' => ['Widgets'],
                    'parameters' => [[
                        'name' => 'widget',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'The widget id.',
                        'schema' => ['type' => 'string'],
                    ]],
                    'responses' => [
                        '200' => [
                            'description' => 'The widget.',
                            'content' => ['application/json' => ['schema' => [
                                'type' => 'object',
                                'properties' => ['data' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer', 'description' => 'The widget id.'],
                                        'label' => ['type' => ['string', 'null']],
                                    ],
                                ]],
                            ]]],
                        ],
                        '404' => ['description' => 'The widget was not found.'],
                    ],
                ],
            ],
        ],
    ];

    TemplateServer::tool(ScaffoldOpenApi::class, [
        'openapi' => json_encode($schema, JSON_THROW_ON_ERROR),
        'dry_run' => true,
    ])->assertOk()
        ->assertHasNoErrors()
        ->assertSee('app/Modules/Api/Widget/Show/WidgetShowResponse.php')
        ->assertSee('public int $id;')
        ->assertSee('public ?string $label;')
        ->assertSee('app/Modules/Api/Widget/WidgetParameter.php')
        ->assertSee('The widget was not found.');
});

test('it prefixes external paths and uses operation ids to avoid conventional module collisions', function (): void {
    $schema = <<<'YAML'
        openapi: 3.0.4
        info:
          title: Pets
          version: 1.0.0
        paths:
          /pet/findByStatus:
            get:
              operationId: findPetsByStatus
              summary: Find pets by status.
              tags: [pet]
              responses:
                '200':
                  description: Matching pets.
        YAML;

    TemplateServer::tool(ScaffoldOpenApi::class, ['openapi' => $schema, 'dry_run' => true])
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSee('app/Modules/Api/Pet/FindByStatus/PetFindByStatusController.php')
        ->assertSee("case pet_find_by_status = self::prefix.'/pet/findByStatus';");
});

test('it rejects a non-openapi 3 schema', function (): void {
    TemplateServer::tool(ScaffoldOpenApi::class, [
        'openapi' => '{"swagger":"2.0"}',
        'dry_run' => true,
    ])->assertHasErrors()->assertSee('Only OpenAPI 3.x schemas are supported.');
});
