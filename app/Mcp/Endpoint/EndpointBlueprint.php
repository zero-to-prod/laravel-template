<?php

namespace App\Mcp\Endpoint;

/** @phpstan-type ErrorStatus array{status: int, description: string} */
readonly class EndpointBlueprint
{
    /**
     * @param  list<string>  $tags
     * @param  list<EndpointField>  $requestFields
     * @param  list<EndpointField>  $responseFields
     * @param  list<EndpointParameter>  $pathParameters
     * @param  list<ErrorStatus>  $errorStatuses
     */
    private function __construct(
        public string $module,
        public EndpointApi $api,
        public string $prefix,
        public string $method,
        public string $path,
        public string $routeCase,
        public bool $authenticated,
        public bool $security,
        public bool $paginated,
        public int $successStatus,
        public string $operationId,
        public string $summary,
        public array $tags,
        public string $successDescription,
        public array $requestFields,
        public array $responseFields,
        public array $pathParameters,
        public array $errorStatuses,
    ) {}

    /** @param  array<string, mixed>  $input */
    public static function from(array $input): self
    {
        $module = trim(self::text($input, 'module') ?? '', '/');
        $path = self::text($input, 'path') ?? '';
        $authenticated = ($input['authenticated'] ?? true) === true;
        $successStatus = $input['success_status'] ?? null;
        $api = EndpointApi::tryFrom(self::text($input, 'api') ?? '') ?? EndpointApi::public;

        return new self(
            module: $module,
            api: $api,
            prefix: self::text($input, 'class_prefix') ?? str_replace('/', '', $module),
            method: strtolower(self::text($input, 'method') ?? 'get'),
            path: $path,
            routeCase: self::text($input, 'route_case') ?? self::caseFor($path),
            authenticated: $authenticated,
            security: ($input['security'] ?? $authenticated) === true,
            paginated: ($input['paginated'] ?? false) === true,
            successStatus: is_int($successStatus) ? $successStatus : 200,
            operationId: self::text($input, 'operation_id') ?? '',
            summary: self::text($input, 'summary') ?? '',
            tags: self::tags($input),
            successDescription: self::text($input, 'success_description') ?? '',
            requestFields: self::fields($input, 'request_fields'),
            responseFields: self::fields($input, 'response_fields'),
            pathParameters: self::pathParameters($input),
            errorStatuses: self::errorStatuses($input),
        );
    }

    public function namespace(): string
    {
        return 'App\\Modules\\Api\\'.str_replace('/', '\\', $this->module);
    }

    public function directory(): string
    {
        return 'app/Modules/Api/'.$this->module;
    }

    public function requestClass(): string
    {
        return $this->prefix.'Request';
    }

    public function responseClass(): string
    {
        return $this->prefix.'Response';
    }

    public function schemaClass(): string
    {
        return $this->prefix.'Schema';
    }

    public function controllerClass(): string
    {
        return $this->prefix.'Controller';
    }

    public function testPath(): string
    {
        return 'tests/Behavior/Api/'.$this->prefix.'Test.php';
    }

    public function parameterNamespace(): string
    {
        return rtrim('App\\Modules\\Api\\'.str_replace('/', '\\', $this->parentModule()), '\\');
    }

    public function parameterDirectory(): string
    {
        return rtrim('app/Modules/Api/'.$this->parentModule(), '/');
    }

    public function parameterFqcn(EndpointParameter $EndpointParameter): string
    {
        return $EndpointParameter->class ?? $this->parameterNamespace().'\\'.$EndpointParameter->className();
    }

    /** @return list<string> */
    public function templatedSegments(): array
    {
        preg_match_all('/\{([^}]+)}/', $this->path, $matches);

        return $matches[1];
    }

    public function routesFile(): string
    {
        return $this->api->routesFile($this->authenticated);
    }

    public function hasBody(): bool
    {
        return $this->requestFields !== [];
    }

    public function hasNullableResponse(): bool
    {
        return array_any($this->responseFields, fn ($Field) => $Field->nullable);
    }

    public function declaresUnauthorized(): bool
    {
        return $this->authenticated || $this->security;
    }

    public function routeCaseLine(): string
    {
        return $this->api->routePrefix()
                |> strlen(...)
                |> (fn ($x) => substr($this->path, $x))
                |> (fn ($x) => sprintf("    case %s = self::prefix.'%s';", $this->routeCase, $x));
    }

    public function blankableField(): ?EndpointField
    {
        return array_find($this->requestFields, fn ($Field) => $Field->reachesValidationError());
    }

    /** @return list<string> */
    public function tables(): array
    {
        $tables = [];

        foreach ([...$this->requestFields, ...$this->responseFields] as $Field) {
            if ($Field->table !== null && ! in_array($Field->table, $tables, true)) {
                $tables[] = $Field->table;
            }
        }

        return $tables;
    }

    private function parentModule(): string
    {
        $separator = strrpos($this->module, '/');

        return $separator === false ? '' : substr($this->module, 0, $separator);
    }

    private static function caseFor(string $path): string
    {
        $prefix = str_starts_with($path, EndpointApi::admin->prefix())
            ? EndpointApi::admin->routePrefix()
            : EndpointApi::public->routePrefix();

        $segments = $prefix
                |> strlen(...)
                |> (static fn ($x) => substr($path, $x))
                |> (static fn ($x) => trim($x, '/'));

        return str_replace(['{', '}', '/', '-'], ['', '', '_', '_'], $segments);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<EndpointParameter>
     */
    private static function pathParameters(array $input): array
    {
        $parameters = $input['path_parameters'] ?? [];

        if (! is_array($parameters)) {
            return [];
        }

        $built = [];

        foreach ($parameters as $parameter) {
            if (is_array($parameter)) {
                /** @var array<string, mixed> $parameter */
                $built[] = EndpointParameter::from($parameter);
            }
        }

        return $built;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<string>
     */
    private static function tags(array $input): array
    {
        $tags = $input['tags'] ?? [];

        if (! is_array($tags)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $tag): string => is_string($tag) ? $tag : '', $tags));
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<EndpointField>
     */
    private static function fields(array $input, string $key): array
    {
        $fields = $input[$key] ?? [];

        if (! is_array($fields)) {
            return [];
        }

        $built = [];

        foreach ($fields as $field) {
            if (is_array($field)) {
                /** @var array<string, mixed> $field */
                $built[] = EndpointField::from($field);
            }
        }

        return $built;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<ErrorStatus>
     */
    private static function errorStatuses(array $input): array
    {
        $statuses = $input['error_statuses'] ?? [];

        if (! is_array($statuses)) {
            return [];
        }

        $built = [];

        foreach ($statuses as $status) {
            if (! is_array($status)) {
                continue;
            }

            /** @var array<string, mixed> $status */
            $code = $status['status'] ?? null;

            if (! is_int($code)) {
                continue;
            }

            $built[] = [
                'status' => $code,
                'description' => self::text($status, 'description') ?? '',
            ];
        }

        return $built;
    }

    /** @param  array<string, mixed>  $input */
    private static function text(array $input, string $key): ?string
    {
        $value = $input[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
