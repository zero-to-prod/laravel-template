<?php

namespace App\Modules\Api\Discovery;

use App\Modules\Api\Support\Endpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionException;

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

            if ($controller === null || ! class_exists($controller)) {
                continue;
            }

            $ReflectionAttribute = new ReflectionClass($controller)->getAttributes(Endpoint::class);

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
}
