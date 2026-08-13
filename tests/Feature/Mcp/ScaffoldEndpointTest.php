<?php

use App\Mcp\Servers\TemplateServer;
use App\Mcp\Tools\ScaffoldEndpoint;

/** @return array<string, mixed> */
function scaffoldArguments(): array
{
    return [
        'module' => 'Widget/Index',
        'method' => 'get',
        'path' => '/api/widgets',
        'operation_id' => 'listWidgets',
        'summary' => 'List the widgets of the authenticated user.',
        'tags' => ['Widgets'],
        'success_description' => 'The widgets.',
        'response_fields' => [
            ['name' => 'id', 'type' => 'string', 'table' => 'Users'],
            ['name' => 'label', 'type' => 'string', 'nullable' => true, 'description' => 'What the widget is called.'],
        ],
        'dry_run' => true,
    ];
}

test('it renders the six artifacts without writing them', function (): void {
    $Response = TemplateServer::tool(ScaffoldEndpoint::class, scaffoldArguments());

    $Response->assertOk()
        ->assertHasNoErrors()
        ->assertSee('Nothing was written.')
        ->assertSee('app/Modules/Api/Widget/Index/WidgetIndexResponse.php')
        ->assertSee('app/Modules/Api/Widget/Index/WidgetIndexSchema.php')
        ->assertSee('app/Modules/Api/Widget/Index/WidgetIndexController.php')
        ->assertSee('tests/Behavior/Api/WidgetIndexTest.php')
        ->assertSee('app/Routes/ApiRoute.php')
        ->assertSee('routes/api_auth.php');

    expect(file_exists(base_path('app/Modules/Api/Widget')))->toBeFalse();
});

test('a nullable response field carries the class level Describe', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, scaffoldArguments())
        ->assertOk()
        ->assertSee('#[Describe([Describe::nullable => true])]')
        ->assertSee('public ?string $label;');
});

test('an authenticated endpoint declares the middleware 401', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, scaffoldArguments())
        ->assertOk()
        ->assertSee('SharedSchema::middleware_error_description')
        ->assertSee("case widgets = self::prefix.'/widgets';");
});

test('a body adds the request DTO and the 422', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'module' => 'Widget/Store',
        'method' => 'post',
        'success_status' => 201,
        'request_fields' => [
            ['name' => 'label', 'type' => 'string', 'required' => true, 'description' => 'What to call it.'],
        ],
    ])->assertOk()
        ->assertSee('app/Modules/Api/Widget/Store/WidgetStoreRequest.php')
        ->assertSee('SharedSchema::api_validation_error')
        ->assertSee('a blank label is rejected')
        ->assertSee('api_response()->created(');
});

test('an endpoint with no body and no auth is public and declares no 401', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'authenticated' => false,
        'security' => false,
    ])->assertOk()
        ->assertSee('routes/api.php')
        ->assertDontSee('SharedSchema::middleware_error');
});

test('a templated path writes the shared parameter class beside the modules', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'module' => 'Widget/Show',
        'path' => '/api/widgets/{widget}',
        'route_case' => 'widget',
        'path_parameters' => [
            ['name' => 'widget', 'description' => 'The id of the widget.'],
        ],
    ])->assertOk()
        ->assertSee('app/Modules/Api/Widget/WidgetParameter.php')
        ->assertSee("'parameters' => [WidgetParameter::schema()],")
        ->assertSee('public function __invoke(Request $Request, string $widget): JsonResponse')
        ->assertSee("ApiRoute::widget->url([WidgetParameter::name => 'example'])");
});

test('a templated path handed a parameter class of its own writes none', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'module' => 'Widget/Show',
        'path' => '/api/widgets/{widget}',
        'route_case' => 'widget',
        'path_parameters' => [
            ['name' => 'widget', 'description' => 'The id of the widget.', 'class' => 'App\Modules\Api\Shared\WidgetParameter'],
        ],
    ])->assertOk()
        ->assertSee('use App\Modules\Api\Shared\WidgetParameter;')
        ->assertDontSee('app/Modules/Api/Widget/WidgetParameter.php');
});

test('a paginated index declares the query parameters and a pagination object', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'paginated' => true,
        'response_fields' => [
            ['name' => 'widgets', 'type' => 'array', 'items_of' => 'App\Modules\Api\User\Show\UserShowResponse'],
        ],
    ])->assertOk()
        ->assertSee("'parameters' => [...PaginationParameters::schema()],")
        ->assertSee('use App\Modules\Api\Support\PaginationResponse;')
        ->assertSee('Schema::items => UserShowResponse::data(),')
        ->assertSee('return PaginationResponse::data();')
        ->assertSee('public array $pagination;');
});

test('a templated segment with no parameter is refused', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'path' => '/api/widgets/{widget}',
    ])->assertHasErrors()
        ->assertSee('Every templated segment needs one entry');
});

test('a module that is already there is not overwritten', function (): void {
    TemplateServer::tool(ScaffoldEndpoint::class, [
        ...scaffoldArguments(),
        'module' => 'User/Show',
        'class_prefix' => 'UserShow',
        'dry_run' => false,
    ])->assertHasErrors()
        ->assertSee('app/Modules/Api/User/Show/UserShowResponse.php');
});
