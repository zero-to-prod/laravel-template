<?php

namespace App\Modules\Api\Discovery;

use App\Modules\Api\Support\Endpoint;
use App\Modules\Api\Support\Field;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionException;
use Zerotoprod\DataModel\Describe;

readonly class DiscoveryController
{
    /**
     * @throws ReflectionException
     */
    public function __invoke(): JsonResponse
    {
        $endpoints = [];

        foreach (Route::getRoutes() as $route) {
            $controller = $route->getControllerClass();

            if ($controller === null || !class_exists($controller)) {
                continue;
            }

            $reflection = new ReflectionClass($controller);
            $attributes = $reflection->getAttributes(Endpoint::class);

            if (empty($attributes)) {
                continue;
            }

            /** @var Endpoint $endpoint */
            $endpoint = $attributes[0]->newInstance();

            $methods = array_values(
                array_filter(
                    $route->methods(),
                    static fn(string $method) => $method !== 'HEAD',
                )
            );

            $uri = '/'.$route->uri();

            // Extract path params from URI pattern
            preg_match_all('/\{(\w+)}/', $uri, $matches);
            $pathParams = $matches[1];

            // Detect auth requirement from middleware
            $middleware = $route->middleware();
            $auth = in_array('auth:sanctum', $middleware, true);

            $entry = [
                'method' => implode('|', $methods),
                'uri' => $uri,
                'description' => $endpoint->description,
                'auth' => $auth,
            ];

            if (!empty($pathParams)) {
                $entry['path_params'] = $pathParams;
            }

            if ($endpoint->request_schema !== null) {
                $entry['request_schema'] = $this->buildSchema(new ReflectionClass($endpoint->request_schema));
            }

            if (!empty($endpoint->errors)) {
                $entry['errors'] = $endpoint->errors;
            }

            if ($endpoint->response_schema !== null) {
                $entry['response_type'] = class_basename($endpoint->response_schema);
                $entry['response_schema'] = $this->buildSchema(new ReflectionClass($endpoint->response_schema));
            }

            if (!empty($endpoint->accepts)) {
                $entry['accepts'] = $endpoint->accepts;
            }

            $endpoints[] = $entry;
        }

        return response()->json([
            'success' => true,
            'message' => 'api_discovery',
            'type' => 'api_discovery',
            'data' => $endpoints,
            'errors' => [],
        ]);
    }

    private function buildSchema(ReflectionClass $reflection): array
    {
        $schema = [];

        foreach ($reflection->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }

            $type = $property->getType();
            $fieldAttributes = $property->getAttributes(Field::class);
            $field = !empty($fieldAttributes) ? $fieldAttributes[0]->newInstance() : null;
            $description = $field?->description ?? '';
            $rules = $field?->rules ?? '';

            $entry = [
                'type' => $type?->getName() ?? 'mixed',
                'nullable' => $type?->allowsNull() ?? true,
                ...($description !== '' ? ['description' => $description] : []),
                ...($rules !== '' ? ['rules' => $rules] : []),
            ];

            $describeAttributes = $property->getAttributes(Describe::class);
            if (!empty($describeAttributes)) {
                $args = $describeAttributes[0]->getArguments()[0] ?? [];
                $nestedType = $args['type'] ?? null;
                if ($nestedType !== null && class_exists($nestedType)) {
                    $entry['type'] = 'array';
                    $entry['items_type'] = class_basename($nestedType);
                    $entry['items'] = $this->buildSchema(new ReflectionClass($nestedType));
                }
            }

            $schema[$property->getName()] = $entry;
        }

        return $schema;
    }
}
