<?php

namespace Tests\Support;

use ZeroToProd\LaravelOpenapi\SchemaGenerator;

final class OasDocument
{
    private const string JSON = 'application/json';

    private static ?self $generated = null;

    /** @param  array<string, mixed>  $document */
    private function __construct(private readonly array $document) {}

    /** The document this application publishes. Generated once, the routes being fixed. */
    public static function generated(): self
    {
        return self::$generated ??= new self(app(SchemaGenerator::class)->document());
    }

    /**
     * Every JSON body the document publishes, keyed by the operation and the
     * direction it describes.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodySchemas(): array
    {
        $schemas = [];

        foreach (self::keys($this->document, 'paths') as $path) {
            foreach (self::keys($this->document, 'paths', $path) as $method) {
                $request = $this->requestSchema($path, $method);

                if ($request !== null) {
                    $schemas[$method.' '.$path.' request'] = $request;
                }

                foreach ($this->statuses($path, $method) as $status) {
                    $response = $this->responseSchema($path, $method, $status);

                    if ($response !== null) {
                        $schemas[$method.' '.$path.' response '.$status] = $response;
                    }
                }
            }
        }

        return $schemas;
    }

    /** @return array<string, mixed>|null */
    public function requestSchema(string $path, string $method): ?array
    {
        return $this->resolving(self::at($this->document, 'paths', $path, $method, 'requestBody', 'content', self::JSON, 'schema'));
    }

    /** @return array<string, mixed>|null */
    public function responseSchema(string $path, string $method, string $status): ?array
    {
        return $this->resolving(self::at($this->document, 'paths', $path, $method, 'responses', $status, 'content', self::JSON, 'schema'));
    }

    /**
     * The statuses the operation declares a response for.
     *
     * @return list<string>
     */
    public function statuses(string $path, string $method): array
    {
        return self::keys($this->document, 'paths', $path, $method, 'responses');
    }

    /**
     * @param  array<string, mixed>|null  $schema
     * @return array<string, mixed>|null
     */
    private function resolving(?array $schema): ?array
    {
        return $schema === null ? null : $this->resolved($schema);
    }

    /**
     * Substitutes a `$ref` for what it points at, throughout. The document is
     * generated from attributes rather than written, so it holds no cycle for
     * this to recur into.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function resolved(array $schema): array
    {
        $ref = $schema['$ref'] ?? null;

        if (is_string($ref)) {
            $schema = self::at($this->document, ...explode('/', ltrim($ref, '#/'))) ?? [];
        }

        foreach ($schema as $keyword => $constraint) {
            if (is_array($constraint)) {
                /** @var array<string, mixed> $constraint */
                $schema[$keyword] = $this->resolved($constraint);
            }
        }

        return $schema;
    }

    /** @return list<string> */
    private static function keys(mixed $value, int|string ...$keys): array
    {
        return array_map(strval(...), array_keys(self::at($value, ...$keys) ?? []));
    }

    /** @return array<string, mixed>|null */
    private static function at(mixed $value, int|string ...$keys): ?array
    {
        foreach ($keys as $key) {
            if (! is_array($value) || ! array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        if (! is_array($value)) {
            return null;
        }

        /** @var array<string, mixed> $value */
        return $value;
    }
}
