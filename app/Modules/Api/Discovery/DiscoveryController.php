<?php

namespace App\Modules\Api\Discovery;

use App\Modules\Api\Endpoint;
use App\Modules\Api\Field;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use ReflectionClass;

class DiscoveryController
{
    public function __invoke(): JsonResponse
    {
        $endpoints = [];

        foreach (Route::getRoutes() as $route) {
            $controller = $route->getControllerClass();

            if ($controller === null || ! class_exists($controller)) {
                continue;
            }

            $reflection = new ReflectionClass($controller);
            $attributes = $reflection->getAttributes(Endpoint::class);

            if (empty($attributes)) {
                continue;
            }

            $endpoint = $attributes[0]->newInstance();

            $methods = array_values(array_filter(
                $route->methods(),
                static fn (string $method) => $method !== 'HEAD',
            ));

            $uri = '/'.$route->uri();

            // Extract path params from URI pattern
            preg_match_all('/\{(\w+)\}/', $uri, $matches);
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

            if (! empty($pathParams)) {
                $entry['path_params'] = $pathParams;
            }

            if ($endpoint->request !== null) {
                $entry['request_schema'] = $this->buildSchema(new ReflectionClass($endpoint->request));
            }

            if (! empty($endpoint->errors)) {
                $entry['errors'] = $endpoint->errors;
            }

            if ($endpoint->response !== null) {
                $entry['response_type'] = class_basename($endpoint->response);
                $entry['response_schema'] = $this->buildSchema(new ReflectionClass($endpoint->response));
            }

            if (! empty($endpoint->accepts)) {
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
            if (! $property->isPublic()) {
                continue;
            }

            $type = $property->getType();
            $fieldAttributes = $property->getAttributes(Field::class);
            $field = ! empty($fieldAttributes) ? $fieldAttributes[0]->newInstance() : null;
            $description = $field?->description ?? '';
            $rules = $field?->rules ?? '';

            $schema[$property->getName()] = [
                'type' => $type?->getName() ?? 'mixed',
                'nullable' => $type?->allowsNull() ?? true,
                ...($description !== '' ? ['description' => $description] : []),
                ...($rules !== '' ? ['rules' => $rules] : []),
            ];
        }

        return $schema;
    }
}