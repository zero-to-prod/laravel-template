<?php

namespace App\Mcp\OpenApi;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

class OpenApiEndpointMapper
{
    /** @var array<string, mixed> */
    private array $document;

    /** @return list<array<string, mixed>> */
    public function map(string $schema): array
    {
        $decoded = json_validate($schema) ? json_decode($schema, true) : Yaml::parse($schema);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('The OpenAPI schema must be a JSON or YAML object.');
        }

        $version = $decoded['openapi'] ?? null;

        if (! is_string($version) || ! str_starts_with($version, '3.')) {
            throw new InvalidArgumentException('Only OpenAPI 3.x schemas are supported.');
        }

        $this->document = $decoded;
        $endpoints = [];
        $modules = [];

        $paths = $decoded['paths'] ?? [];

        if (! is_array($paths)) {
            throw new InvalidArgumentException('The OpenAPI paths value must be an object.');
        }

        foreach ($paths as $path => $pathItem) {
            if (! is_string($path) || ! is_array($pathItem)) {
                continue;
            }

            foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
                $operation = $pathItem[$method] ?? null;

                if (is_array($operation)) {
                    /** @var array<string, mixed> $pathItem */
                    /** @var array<string, mixed> $operation */
                    $endpoint = $this->operation($method, $path, $pathItem, $operation);
                    $module = $endpoint['module'];

                    if (is_string($module) && in_array($module, $modules, true)) {
                        $resource = Str::before($module, '/');
                        $endpoint['module'] = $resource.'/'.$this->specificAction($this->text($operation, 'operationId'), $resource);
                    }

                    $modules[] = $endpoint['module'];
                    $endpoints[] = $endpoint;
                }
            }
        }

        if ($endpoints === []) {
            throw new InvalidArgumentException('The OpenAPI schema has no supported operations.');
        }

        return $endpoints;
    }

    /**
     * @param  array<string, mixed>  $pathItem
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function operation(string $method, string $path, array $pathItem, array $operation): array
    {
        $operationTags = $operation['tags'] ?? [];
        $tags = is_array($operationTags) ? array_values(array_filter($operationTags, 'is_string')) : [];
        $resource = Str::studly(Str::singular($tags[0] ?? $this->resourceFrom($path)));
        $action = $this->action($method, $path, $this->text($operation, 'operationId'), $resource);
        $responses = is_array($operation['responses'] ?? null) ? $operation['responses'] : [];
        [$successStatus, $success] = $this->success($responses);
        $security = array_key_exists('security', $operation)
            ? $operation['security'] !== []
            : ($this->document['security'] ?? []) !== [];

        return [
            'module' => $resource.'/'.$action,
            'method' => $method,
            'path' => '/api/'.ltrim($path, '/'),
            'route_case' => $this->routeCase($path),
            'authenticated' => $security,
            'security' => $security,
            'success_status' => $successStatus,
            'operation_id' => $this->text($operation, 'operationId') ?? Str::camel($action.$resource),
            'summary' => $this->text($operation, 'summary') ?? $this->text($operation, 'description') ?? $action.' '.$resource.'.',
            'tags' => $tags === [] ? [Str::plural($resource)] : $tags,
            'success_description' => $this->text($success, 'description') ?? 'The successful response.',
            'path_parameters' => $this->parameters($pathItem, $operation),
            'request_fields' => $this->requestFields($operation),
            'response_fields' => $this->responseFields($success),
            'error_statuses' => $this->errors($responses),
        ];
    }

    /**
     * @param  array<int|string, mixed>  $responses
     * @return array{int, array<string, mixed>}
     */
    private function success(array $responses): array
    {
        foreach ([200, 201] as $status) {
            $response = $responses[(string) $status] ?? null;

            if (is_array($response)) {
                return [$status, $this->resolve($response)];
            }
        }

        throw new InvalidArgumentException('Each operation needs a 200 or 201 response.');
    }

    /**
     * @param  array<string, mixed>  $pathItem
     * @param  array<string, mixed>  $operation
     * @return list<array<string, mixed>>
     */
    private function parameters(array $pathItem, array $operation): array
    {
        $result = [];

        $pathParameters = $pathItem['parameters'] ?? [];
        $operationParameters = $operation['parameters'] ?? [];

        if (! is_array($pathParameters) || ! is_array($operationParameters)) {
            throw new InvalidArgumentException('OpenAPI parameters must be arrays.');
        }

        foreach ([...$pathParameters, ...$operationParameters] as $parameter) {
            if (! is_array($parameter)) {
                continue;
            }

            $parameter = $this->resolve($parameter);

            if (($parameter['in'] ?? null) === 'path' && is_string($parameter['name'] ?? null)) {
                $result[] = [
                    'name' => $parameter['name'],
                    'description' => $this->text($parameter, 'description') ?? 'The '.$parameter['name'].' path parameter.',
                ];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return list<array<string, mixed>>
     */
    private function requestFields(array $operation): array
    {
        $body = $operation['requestBody'] ?? null;

        if (! is_array($body)) {
            return [];
        }

        $body = $this->resolve($body);
        $schema = $this->jsonSchema($body);

        return $schema === null ? [] : $this->fields($schema, true);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return list<array<string, mixed>>
     */
    private function responseFields(array $response): array
    {
        $schema = $this->jsonSchema($response);

        if ($schema === null) {
            return [];
        }

        $properties = $schema['properties'] ?? null;
        $data = is_array($properties) ? ($properties['data'] ?? null) : null;

        if (is_array($data)) {
            return $this->fields($this->object($data), false);
        }

        return $this->fields($schema, false);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return list<array<string, mixed>>
     */
    private function fields(array $schema, bool $request): array
    {
        $schema = $this->resolve($schema);
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];
        $fields = [];

        if (! is_array($properties)) {
            return [];
        }

        foreach ($properties as $name => $property) {
            if (! is_string($name) || ! is_array($property)) {
                continue;
            }

            $property = $this->resolve($property);
            $type = $property['type'] ?? 'string';
            $nullable = ($property['nullable'] ?? false) === true;

            if (is_array($type)) {
                $nullable = $nullable || in_array('null', $type, true);
                $type = array_find($type, static fn (mixed $value): bool => $value !== 'null') ?? 'string';
            }

            $fields[] = array_filter([
                'name' => $name,
                'type' => match ($type) {
                    'integer' => 'int',
                    'number' => 'float',
                    'boolean' => 'bool',
                    'array' => 'array',
                    default => 'string',
                },
                'nullable' => $nullable,
                'required' => $request && in_array($name, is_array($required) ? $required : [], true),
                'description' => $this->text($property, 'description'),
            ], static fn (mixed $value): bool => $value !== null && $value !== false);
        }

        return $fields;
    }

    /**
     * @param  array<int|string, mixed>  $responses
     * @return list<array{status: int, description: string}>
     */
    private function errors(array $responses): array
    {
        $result = [];

        foreach ([400, 403, 404, 409, 415] as $status) {
            $response = $responses[(string) $status] ?? null;

            if (is_array($response)) {
                $response = $this->resolve($response);
                $result[] = ['status' => $status, 'description' => $this->text($response, 'description') ?? 'The request failed.'];
            }
        }

        return $result;
    }

    private function action(string $method, string $path, ?string $operationId, string $resource): string
    {
        $conventional = match ($method) {
            'get' => str_ends_with($path, '}') ? 'Show' : 'Index',
            'post' => 'Store',
            'put', 'patch' => 'Update',
            'delete' => 'Delete',
            default => throw new InvalidArgumentException('Unsupported HTTP method '.$method.'.'),
        };

        if ($operationId === null || $this->isConventionalOperation($operationId, $conventional)) {
            return $conventional;
        }

        return $this->specificAction($operationId, $resource);
    }

    private function isConventionalOperation(string $operationId, string $action): bool
    {
        $operation = strtolower($operationId);

        return match ($action) {
            'Index' => str_starts_with($operation, 'list') || str_starts_with($operation, 'getall'),
            'Show' => str_starts_with($operation, 'get') || str_starts_with($operation, 'show'),
            'Store' => str_starts_with($operation, 'add') || str_starts_with($operation, 'create') || str_starts_with($operation, 'store'),
            'Update' => str_starts_with($operation, 'update'),
            'Delete' => str_starts_with($operation, 'delete') || str_starts_with($operation, 'remove'),
            default => false,
        };
    }

    private function specificAction(?string $operationId, string $resource): string
    {
        $specific = str_ireplace(
            [Str::plural($resource), Str::singular($resource)],
            '',
            Str::studly($operationId ?? ''),
        );

        return $specific === '' ? 'Endpoint' : $specific;
    }

    private function resourceFrom(string $path): string
    {
        $segments = explode('/', $path)
            |> (static fn (array $segments): array => array_filter($segments, static fn (string $segment): bool => $segment !== '' && $segment !== 'api' && ! str_starts_with($segment, '{')))(...)
            |> array_values(...);

        return end($segments) ?: 'Endpoint';
    }

    private function routeCase(string $path): string
    {
        $case = trim(str_replace(['{', '}'], '', $path), '/');

        return Str::snake(str_replace(['/', '-'], '_', $case));
    }

    /**
     * @param  array<string, mixed>  $container
     * @return array<string, mixed>|null
     */
    private function jsonSchema(array $container): ?array
    {
        $content = $container['content'] ?? null;
        $json = is_array($content) ? ($content['application/json'] ?? null) : null;
        $schema = is_array($json) ? ($json['schema'] ?? null) : null;

        if (! is_array($schema)) {
            return null;
        }

        return $this->resolve($schema);
    }

    /**
     * @param  array<mixed, mixed>  $value
     * @return array<string, mixed>
     */
    private function resolve(array $value): array
    {
        $value = $this->object($value);
        $reference = $value['$ref'] ?? null;

        if (! is_string($reference) || ! str_starts_with($reference, '#/')) {
            return $value;
        }

        $resolved = $this->document;

        foreach (explode('/', substr($reference, 2)) as $segment) {
            $segment = str_replace(['~1', '~0'], ['/', '~'], $segment);
            $resolved = is_array($resolved) ? ($resolved[$segment] ?? null) : null;
        }

        if (! is_array($resolved)) {
            throw new InvalidArgumentException('Could not resolve OpenAPI reference '.$reference.'.');
        }

        return $this->object($resolved);
    }

    /**
     * OpenAPI objects can only have string keys.
     *
     * @param  array<mixed, mixed>  $value
     * @return array<string, mixed>
     */
    private function object(array $value): array
    {
        $object = [];

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $object[$key] = $item;
            }
        }

        return $object;
    }

    /** @param array<string, mixed> $values */
    private function text(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
