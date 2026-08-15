<?php

use App\Models\User;
use App\Modules\Api\Support\AdminApiSchema;
use App\Modules\Api\Support\SchemaController;
use App\Modules\Api\Support\SchemaGenerator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\OpenApi\AdminSchemaController;

beforeEach(function (): void {
    Route::get('/admin/schema-test', AdminSchemaController::class);
});

test('admin operations are isolated from the public document', function (): void {
    $this->getJson(Config::string('openapi.schemas.public.route.uri'))
        ->assertOk()
        ->assertJsonMissingPath('paths./admin~1schema-test');
});

test('the admin document requires an authenticated administrator', function (): void {
    $uri = Config::string('openapi.schemas.admin.route.uri');

    $this->get($uri)->assertRedirect();

    $this->actingAs(User::factory()->createOne())
        ->get($uri)
        ->assertForbidden();

    $this->actingAs(adminUser())
        ->getJson($uri)
        ->assertOk()
        ->assertJsonPath('info.title', Config::string('app.name').' Admin API')
        ->assertJsonPath('paths./admin/schema-test.get.operationId', 'adminSchemaTest');
});

test('an absent or invalid schema configuration is rejected', function (?string $attribute): void {
    Config::set('openapi.schemas.invalid', $attribute === null ? [] : ['attribute' => $attribute]);

    expect(fn () => app(SchemaController::class)('invalid', app(Router::class)))
        ->toThrow(RuntimeException::class, 'OpenAPI schema [invalid] is not configured.');
})->with([
    'absent attribute' => null,
    'invalid attribute' => stdClass::class,
]);

test('a route whose controller action does not exist is skipped', function (): void {
    Route::get('/missing-schema-action', AdminSchemaController::class.'@missing');

    $document = (new SchemaGenerator(
        app(Router::class),
        AdminApiSchema::class,
        ['info' => ['title' => 'Admin']],
    ))->document();

    expect($document)->toHaveKey('paths./admin/schema-test')
        ->and(data_get($document, 'paths./missing-schema-action'))->toBeNull();
});
