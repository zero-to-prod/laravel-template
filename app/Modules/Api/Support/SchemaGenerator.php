<?php

namespace App\Modules\Api\Support;

use Illuminate\Routing\Router;
use ReflectionAttribute;
use ReflectionMethod;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class SchemaGenerator
{
    /**
     * @param  class-string<ApiSchema>  $attribute
     * @param  array<string, mixed>  $document
     */
    public function __construct(
        private Router $router,
        private string $attribute,
        private array $document,
    ) {}

    /** @return array<string, mixed> */
    public function document(): array
    {
        $paths = [];
        $components = [];

        foreach ($this->router->getRoutes()->getRoutes() as $route) {
            $controller = $route->getControllerClass();

            if ($controller === null) {
                continue;
            }

            $method = $route->getActionMethod();
            $method = $method === $controller ? '__invoke' : $method;

            if (! method_exists($controller, $method)) {
                continue;
            }

            $attribute = (new ReflectionMethod($controller, $method))
                ->getAttributes($this->attribute, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

            if ($attribute === null) {
                continue;
            }

            $schema = $attribute->newInstance()->resolve();
            $paths[] = $schema['paths'] ?? [];
            $components[] = $schema['components'] ?? [];
        }

        $paths = array_replace_recursive([], ...$paths);
        $components = array_replace_recursive([], ...$components);
        ksort($paths);

        return array_replace_recursive(
            $this->document,
            ['paths' => $paths],
            $components === [] ? [] : ['components' => $components],
        );
    }
}
