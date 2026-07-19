<?php

namespace App\Modules\Api\Schema;

use App\Helpers\CacheKeys;
use App\Modules\Api\Support\Endpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

readonly class SchemaController
{
    /**
     * @throws ReflectionException
     */
    public function __invoke(): JsonResponse
    {
        return api_response()->ok(
            app()->isProduction()
                ? Cache::rememberForever(CacheKeys::api_schema, $this->buildSchema(...))
                : $this->buildSchema(),
        );
    }

    /**
     * @throws ReflectionException
     */
    private function buildSchema(): array
    {
        $endpoints = [];

        foreach (Route::getRoutes() as $route) {
            $controller = $route->getControllerClass();

            if ($controller === null || ! class_exists($controller)) {
                continue;
            }

            $ReflectionClass = new ReflectionClass($controller);
            $ReflectionAttribute = $ReflectionClass->getAttributes(Endpoint::class);

            if (empty($ReflectionAttribute)) {
                continue;
            }

            /** @var Endpoint $endpoint */
            $endpoint = $ReflectionAttribute[0]->newInstance();

            $methods = array_values(
                array_filter(
                    $route->methods(),
                    static fn (string $method) => $method !== 'HEAD',
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

            if (! empty($pathParams)) {
                $entry['path_params'] = $pathParams;
            }

            if ($endpoint->request_schema !== null) {
                $entry['request_schema'] = build_schema($endpoint->request_schema);
            }

            if (! empty($endpoint->errors)) {
                $entry['errors'] = $endpoint->errors;
            }

            if ($endpoint->response_schema !== null) {
                $entry['response_type'] = class_basename($endpoint->response_schema);
                $entry['response_schema'] = build_schema($endpoint->response_schema);
            }

            $has_body = ! empty(array_intersect($methods, ['POST', 'PUT', 'PATCH']));

            if (! empty($endpoint->accepts)) {
                $entry['accepts'] = $endpoint->accepts;
            } elseif ($has_body && $endpoint->request_schema !== null) {
                $entry['accepts'] = [Endpoint::json];
            }

            $ReturnType = $ReflectionClass->getMethod('__invoke')->getReturnType();
            $content_type = $ReturnType instanceof ReflectionNamedType
                ? Endpoint::content_types[$ReturnType->getName()] ?? null
                : null;

            if ($content_type !== null) {
                $entry['content_type'] = $content_type;
            }

            $endpoints[] = $entry;
        }

        return $endpoints;
    }
}
