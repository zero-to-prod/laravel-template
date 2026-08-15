<?php

namespace App\Mcp\Endpoint;

use App\Helpers\Request;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Modules\Api\Support\PublicApiSchema;
use App\Modules\Api\Support\Response;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\ApiRoute;
use Illuminate\Http\JsonResponse;
use Zerotoprod\DataModel\Describe;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

readonly class EndpointRenderer
{
    public function __construct(private EndpointBlueprint $Blueprint) {}

    /** @return array<string, string> */
    public function files(): array
    {
        $files = [];

        if ($this->Blueprint->hasBody()) {
            $files[$this->Blueprint->directory().'/'.$this->Blueprint->requestClass().'.php'] = $this->request();
        }

        $files[$this->Blueprint->directory().'/'.$this->Blueprint->responseClass().'.php'] = $this->response();
        $files[$this->Blueprint->directory().'/'.$this->Blueprint->schemaClass().'.php'] = $this->schema();
        $files[$this->Blueprint->directory().'/'.$this->Blueprint->controllerClass().'.php'] = $this->controller();
        $files[$this->Blueprint->testPath()] = $this->test();

        return $files;
    }

    /** @return array<string, string> */
    public function parameters(): array
    {
        $files = [];

        foreach ($this->Blueprint->pathParameters as $EndpointParameter) {
            if ($EndpointParameter->class !== null) {
                continue;
            }

            $files[$this->Blueprint->parameterDirectory().'/'.$EndpointParameter->className().'.php'] = $this->parameter($EndpointParameter);
        }

        return $files;
    }

    private function parameter(EndpointParameter $EndpointParameter): string
    {
        return sprintf(
            <<<'PHP'
                <?php

                namespace %s;

                use ZeroToProd\LaravelOpenapi\ApiSchema;
                use ZeroToProd\SchemaValidator\Property;

                /**
                 * The `{%s}` path parameter, shared by the operations keyed on it.
                 *
                 * @phpstan-import-type Parameter from ApiSchema
                 */
                readonly class %s
                {
                    public const string name = '%s';

                    /**
                     * Declared as a string rather than as the column schema: a path segment
                     * arrives as text, and a narrower type makes every request to the
                     * operation fail parameter validation before the controller is reached.
                     *
                     * @return Parameter
                     */
                    public static function schema(): array
                    {
                        return [
                            'name' => self::name,
                            'in' => 'path',
                            'required' => true,
                            'description' => '%s',
                            'schema' => [Property::type => Property::string],
                        ];
                    }
                }

                PHP,
            $this->Blueprint->parameterNamespace(),
            $EndpointParameter->name,
            $EndpointParameter->className(),
            $EndpointParameter->name,
            $this->escape($EndpointParameter->description),
        );
    }

    public function request(): string
    {
        $imports = ['App\Helpers\DataModel', Request::class, 'App\Modules\Api\Support\HasRequestSchema'];

        foreach ($this->tablesOf($this->Blueprint->requestFields) as $table) {
            $imports[] = 'App\Sources\Db\App\\'.$table;
        }

        foreach ($this->Blueprint->requestFields as $Field) {
            if ($Field->nullable) {
                $imports[] = Describe::class;
            }

            if ($Field->table === null || $Field->description !== null || $Field->nullable) {
                $imports[] = Property::class;
            }

            if ($Field->table === null && $Field->type === 'array') {
                $imports[] = Schema::class;
            }
        }

        $fields = [];

        foreach ($this->Blueprint->requestFields as $Field) {
            $fields[] = $this->requestField($Field);
        }

        return $this->file($imports, sprintf(
            "readonly class %s\n{\n    use DataModel;\n    use HasRequestSchema;\n\n%s}\n",
            $this->Blueprint->requestClass(),
            implode("\n", $fields),
        ));
    }

    public function response(): string
    {
        $imports = ['App\Helpers\DataModel', 'App\Modules\Api\Support\HasResponseSchema'];

        if ($this->Blueprint->responseFields !== [] || $this->Blueprint->paginated) {
            $imports[] = Response::class;
        }

        foreach ($this->tablesOf($this->Blueprint->responseFields) as $table) {
            $imports[] = 'App\Sources\Db\App\\'.$table;
        }

        foreach ($this->Blueprint->responseFields as $Field) {
            if ($Field->itemsOf !== null) {
                $imports[] = $Field->itemsOf;
                $imports[] = Schema::class;
            }
        }

        if ($this->Blueprint->hasNullableResponse()) {
            $imports[] = Describe::class;
        }

        $fields = [];

        foreach ($this->Blueprint->responseFields as $Field) {
            $fields[] = $this->responseField($Field);
        }

        if ($this->Blueprint->paginated) {
            $imports[] = PaginationResponse::class;
            $fields[] = $this->paginationField();
        }

        $describe = $this->Blueprint->hasNullableResponse() ? "#[Describe([Describe::nullable => true])]\n" : '';

        return $this->file($imports, sprintf(
            "%sreadonly class %s\n{\n    use DataModel;\n    use HasResponseSchema;\n%s}\n",
            $describe,
            $this->Blueprint->responseClass(),
            $fields === [] ? '' : "\n".implode("\n", $fields),
        ));
    }

    public function schema(): string
    {
        $imports = [
            DescribesOperation::class,
            SharedSchema::class,
            ApiRoute::class,
            'ReflectionException',
            ApiSchema::class,
        ];

        foreach ($this->Blueprint->pathParameters as $EndpointParameter) {
            $imports[] = $this->Blueprint->parameterFqcn($EndpointParameter);
        }

        if ($this->Blueprint->paginated) {
            $imports[] = PaginationParameters::class;
        }

        $operation = sprintf(
            "                        'operationId' => '%s',\n                        'summary' => '%s',\n                        'tags' => [%s],\n",
            $this->escape($this->Blueprint->operationId),
            $this->escape($this->Blueprint->summary),
            implode(', ', array_map(fn (string $tag): string => "'".$this->escape($tag)."'", $this->Blueprint->tags)),
        );

        if ($this->Blueprint->security) {
            $operation .= "                        'security' => [[SharedSchema::bearer => []]],\n";
        }

        $parameters = array_map(
            static fn (EndpointParameter $EndpointParameter): string => $EndpointParameter->className().'::schema()',
            $this->Blueprint->pathParameters,
        );

        if ($this->Blueprint->paginated) {
            // Spread: the shared class declares more than one parameter.
            $parameters[] = '...PaginationParameters::schema()';
        }

        if ($parameters !== []) {
            $operation .= sprintf("                        'parameters' => [%s],\n", implode(', ', $parameters));
        }

        if ($this->Blueprint->hasBody()) {
            $operation .= sprintf(
                "                        'requestBody' => [\n                            'required' => true,\n                            'content' => [\n                                'application/json' => ['schema' => %s::schema()],\n                            ],\n                        ],\n",
                $this->Blueprint->requestClass(),
            );
        }

        // Declared ascending, the way every other schema in the app reads.
        $declared = [
            $this->Blueprint->successStatus => sprintf(
                "                            '%d' => [\n                                'description' => '%s',\n                                'content' => [\n                                    'application/json' => ['schema' => %s::schema()],\n                                ],\n                            ],\n",
                $this->Blueprint->successStatus,
                $this->escape($this->Blueprint->successDescription),
                $this->Blueprint->responseClass(),
            ),
        ];

        if ($this->Blueprint->declaresUnauthorized()) {
            $declared[401] = $this->Blueprint->authenticated
                ? $this->errorResponse(401, 'SharedSchema::middleware_error_description', 'SharedSchema::middleware_error')
                : $this->errorResponse(401, "'The token was missing, expired or unrecognised.'", 'SharedSchema::api_error');
        }

        if ($this->Blueprint->hasBody()) {
            $declared[422] = $this->errorResponse(422, "'The request body failed validation.'", 'SharedSchema::api_validation_error');
        }

        foreach ($this->Blueprint->errorStatuses as $status) {
            $declared[$status['status']] = $this->errorResponse($status['status'], "'".$this->escape($status['description'])."'", 'SharedSchema::api_error');
        }

        ksort($declared);

        $responses = implode('', $declared);

        return $this->file($imports, sprintf(
            <<<'PHP'
                /**
                 * @phpstan-import-type PathItem from ApiSchema
                 * @phpstan-import-type Components from ApiSchema
                 */
                readonly class %s implements DescribesOperation
                {
                    /** @return array{paths?: array<string, PathItem>, components?: Components} */
                    public static function schema(): array
                    {
                        return [
                            'components' => SharedSchema::components,
                            'paths' => [
                                ApiRoute::%s->value => [
                                    '%s' => [
                %s                        'responses' => [
                %s                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                }

                PHP,
            $this->Blueprint->schemaClass(),
            $this->Blueprint->routeCase,
            $this->Blueprint->method,
            $operation,
            $responses,
        ));
    }

    public function controller(): string
    {
        $imports = [JsonResponse::class, 'ReflectionException', PublicApiSchema::class];
        $takesRequest = $this->Blueprint->hasBody() || $this->Blueprint->declaresUnauthorized() || $this->Blueprint->paginated;

        if ($takesRequest) {
            $imports[] = 'Illuminate\Http\Request';
        }

        $arguments = $takesRequest ? ['Request $Request'] : [];

        foreach ($this->Blueprint->pathParameters as $EndpointParameter) {
            $arguments[] = 'string $'.$EndpointParameter->name;
        }

        $body = '';

        if ($this->Blueprint->hasBody()) {
            $body .= sprintf(
                "        \$Validator = %s::validator(\$Request->all());\n\n        if (\$Validator->fails()) {\n            return api_response()->unprocessableEntity(\$Validator);\n        }\n\n",
                $this->Blueprint->requestClass(),
            );
        }

        $body .= sprintf(
            "        // @todo Replace this placeholder with what %s does, and hydrate the response from it.\n        return api_response()->%s(%s::from(%s));\n",
            $this->Blueprint->prefix,
            $this->Blueprint->successStatus === 201 ? 'created' : 'ok',
            $this->Blueprint->responseClass(),
            $this->Blueprint->responseFields === [] ? '' : '[]',
        );

        return $this->file($imports, sprintf(
            <<<'PHP'
                readonly class %s
                {
                    #[PublicApiSchema(static function (): array {
                        return %s::schema();
                    })]
                    public function __invoke(%s): JsonResponse
                    {
                %s    }
                }

                PHP,
            $this->Blueprint->controllerClass(),
            $this->Blueprint->schemaClass(),
            implode(', ', $arguments),
            $body,
        ));
    }

    public function test(): string
    {
        $namespace = $this->Blueprint->namespace();
        $imports = [ApiResponse::class, $namespace.'\\'.$this->Blueprint->responseClass(), ApiRoute::class];

        if ($this->Blueprint->hasBody()) {
            $imports[] = $namespace.'\\'.$this->Blueprint->requestClass();
        }

        if ($this->Blueprint->declaresUnauthorized()) {
            $imports[] = 'App\Models\User';
        }

        foreach ($this->Blueprint->pathParameters as $EndpointParameter) {
            $imports[] = $this->Blueprint->parameterFqcn($EndpointParameter);
        }

        $tests = [$this->happyPathTest()];

        if ($this->Blueprint->declaresUnauthorized()) {
            $tests[] = $this->unauthorizedTest();
        }

        if ($this->Blueprint->hasBody()) {
            $tests[] = $this->validationErrorTest();
        }

        foreach ($this->Blueprint->errorStatuses as $status) {
            $tests[] = $this->errorStatusTest($status['status']);
        }

        return "<?php\n\n".$this->imports($imports)."\n".implode("\n", $tests);
    }

    private function happyPathTest(): string
    {
        return sprintf(
            "test('%s', function (): void {\n%s    // @todo Assert the values this endpoint returns. assertMatchesSchema proves shape, not correctness.\n%s\n    \$this->assertMatchesSchema(\$Response)\n        ->assertStatus(%d)\n        ->assertJson([\n            ApiResponse::success => true,\n            ApiResponse::type => class_basename(%s::class),\n        ]);\n});\n",
            $this->escape($this->testName()),
            $this->arrange(),
            $this->call($this->Blueprint->declaresUnauthorized() ? '$token' : null),
            $this->Blueprint->successStatus,
            $this->Blueprint->responseClass(),
        );
    }

    private function unauthorizedTest(): string
    {
        return sprintf(
            "test('an unauthenticated request is rejected', function (): void {\n%s\n    \$this->assertMatchesSchema(\$Response)->assertStatus(401);\n});\n",
            $this->call("'invalid-token'"),
        );
    }

    private function validationErrorTest(): string
    {
        $Field = $this->Blueprint->blankableField();

        if ($Field === null) {
            return sprintf(
                "test('an invalid request body is rejected', function (): void {\n%s    // @todo No required non-nullable string field, so no invalid body the document still admits.\n    // Reach the 422 with a body the document rejects, and drop assertMatchesSchema from this test.\n%s\n    \$this->assertMatchesSchema(\$Response)->assertStatus(422);\n});\n",
                $this->arrange(),
                $this->call($this->Blueprint->declaresUnauthorized() ? '$token' : null),
            );
        }

        return sprintf(
            "test('a blank %s is rejected', function (): void {\n%s    // Blank is a server policy, not a published constraint: the document admits\n    // the empty string, so the request still conforms and the 422 is reachable.\n%s\n    \$this->assertMatchesSchema(\$Response)\n        ->assertStatus(422)\n        ->assertJsonValidationErrors(%s::%s);\n});\n",
            $Field->name,
            $this->arrange(),
            $this->call($this->Blueprint->declaresUnauthorized() ? '$token' : null, [$Field->name => "''"]),
            $this->Blueprint->requestClass(),
            $Field->name,
        );
    }

    private function errorStatusTest(int $status): string
    {
        return sprintf(
            "test('the endpoint answers %d', function (): void {\n%s    // @todo Arrange the state that makes this endpoint answer %d, then assert the error it carries.\n%s\n    \$this->assertMatchesSchema(\$Response)->assertStatus(%d);\n});\n",
            $status,
            $this->arrange(),
            $status,
            $this->call($this->Blueprint->declaresUnauthorized() ? '$token' : null),
            $status,
        );
    }

    private function arrange(): string
    {
        if (! $this->Blueprint->declaresUnauthorized()) {
            return '';
        }

        return "    \$User = User::factory()->createOne();\n    \$token = \$User->createToken('test-device')->plainTextToken;\n\n";
    }

    /** @param  array<string, string>  $overrides  field name => literal */
    private function call(?string $token, array $overrides = []): string
    {
        $caller = $token === null ? '$this' : sprintf('$this->withToken(%s)', $token);
        $route = $this->route();

        if (! $this->Blueprint->hasBody()) {
            return sprintf("    \$Response = %s->%sJson(%s);\n", $caller, $this->Blueprint->method, $route);
        }

        $payload = '';

        foreach ($this->Blueprint->requestFields as $Field) {
            if ($Field->required || array_key_exists($Field->name, $overrides)) {
                $payload .= sprintf(
                    "        %s::%s => %s,\n",
                    $this->Blueprint->requestClass(),
                    $Field->name,
                    $overrides[$Field->name] ?? $Field->placeholder(),
                );
            }
        }

        return sprintf("    \$Response = %s->%sJson(%s, [\n%s    ]);\n", $caller, $this->Blueprint->method, $route, $payload);
    }

    private function route(): string
    {
        if ($this->Blueprint->pathParameters === []) {
            return sprintf('ApiRoute::%s->value', $this->Blueprint->routeCase);
        }

        return array_map(
            static fn (EndpointParameter $EndpointParameter): string => $EndpointParameter->className()."::name => 'example'",
            $this->Blueprint->pathParameters,
        )
                |> (static fn ($x) => implode(', ', $x))
                |> (fn ($x) => sprintf('ApiRoute::%s->url([%s])', $this->Blueprint->routeCase, $x));
    }

    private function testName(): string
    {
        $summary = rtrim($this->Blueprint->summary, '.');

        return lcfirst($summary) === '' ? 'the endpoint responds' : lcfirst($summary);
    }

    private function requestField(EndpointField $EndpointField): string
    {
        $extras = '';

        if ($EndpointField->nullable) {
            $extras .= "                Property::nullable => true,\n";
        }

        if ($EndpointField->description !== null) {
            $extras .= sprintf("                Property::description => '%s',\n", $this->escape($EndpointField->description));
        }

        if ($EndpointField->table !== null) {
            $schema = $extras === ''
                ? sprintf("            return %s::%s->schema();\n", $EndpointField->table, $EndpointField->column)
                : sprintf("            return [\n                ...%s::%s->schema(),\n%s            ];\n", $EndpointField->table, $EndpointField->column, $extras);
        } else {
            $schema = sprintf("            return [\n%s%s            ];\n", $this->literalType($EndpointField), $extras);
        }

        $attribute = sprintf("    #[Request([\n        Request::schema => static function (): array {\n%s        },\n", $schema);

        if ($EndpointField->required) {
            $attribute .= "        Request::required => true,\n";
        }

        $attribute .= "    ])]\n";

        if ($EndpointField->nullable) {
            // The nullable request property needs Describe of its own: the
            // class level flag is a response DTO concern.
            $attribute .= "    #[Describe(['nullable'])]\n";
        }

        return sprintf(
            "    public const string %s = '%s';\n\n%s%s    public %s $%s;\n",
            $EndpointField->name,
            $EndpointField->name,
            $this->arrayDocblock($EndpointField),
            $attribute,
            $EndpointField->declaredType(),
            $EndpointField->name,
        );
    }

    /**
     * The pagination object every index publishes beside its items, read off
     * the shared class so the whole set pages the same way.
     */
    private function paginationField(): string
    {
        return "    public const string pagination = 'pagination';\n\n"
            ."    /** @var array<string, mixed> */\n"
            ."    #[Response([Response::schema => static function (): array {\n"
            ."        return PaginationResponse::data();\n"
            ."    }])]\n"
            ."    public array \$pagination;\n";
    }

    private function responseField(EndpointField $EndpointField): string
    {
        if ($EndpointField->itemsOf !== null) {
            // Read off the class that publishes the item rather than restated,
            // so the list and the single object cannot drift.
            return sprintf(
                "    public const string %s = '%s';\n\n    /** @var list<array<string, mixed>> */\n    #[Response([Response::schema => static function (): array {\n        return [\n            Schema::type => Schema::array,\n            Schema::items => %s::data(),\n        ];\n    }])]\n    public %s $%s;\n",
                $EndpointField->name,
                $EndpointField->name,
                class_basename($EndpointField->itemsOf),
                $EndpointField->declaredType(),
                $EndpointField->name,
            );
        }

        $attribute = $EndpointField->table !== null
            ? sprintf("    #[Response([Response::schema => static function () {\n        return %s::%s->schema();\n    }])]\n", $EndpointField->table, $EndpointField->column)
            : sprintf("    #[Response([Response::description => '%s'])]\n", $this->escape($EndpointField->description ?? $EndpointField->name));

        return sprintf(
            "    public const string %s = '%s';\n\n%s%s    public %s $%s;\n",
            $EndpointField->name,
            $EndpointField->name,
            $this->arrayDocblock($EndpointField),
            $attribute,
            $EndpointField->declaredType(),
            $EndpointField->name,
        );
    }

    /** An array property carries no value type, which phpstan needs at level 9. */
    private function arrayDocblock(EndpointField $EndpointField): string
    {
        if ($EndpointField->type !== 'array') {
            return '';
        }

        return sprintf("    /** @var list<string>%s */\n", $EndpointField->nullable ? '|null' : '');
    }

    private function literalType(EndpointField $EndpointField): string
    {
        return match ($EndpointField->type) {
            'int' => "                Property::type => Property::integer,\n",
            'float' => "                Property::type => Property::number,\n",
            'bool' => "                Property::type => Property::boolean,\n",
            'array' => "                Schema::type => Schema::array,\n                Schema::items => [Property::type => Property::string],\n",
            default => "                Property::type => Property::string,\n",
        };
    }

    private function errorResponse(int $status, string $description, string $ref): string
    {
        return sprintf(
            "                            '%d' => [\n                                'description' => %s,\n                                'content' => [\n                                    'application/json' => [\n                                        'schema' => ['\$ref' => %s],\n                                    ],\n                                ],\n                            ],\n",
            $status,
            $description,
            $ref,
        );
    }

    /**
     * @param  list<EndpointField>  $fields
     * @return list<string>
     */
    private function tablesOf(array $fields): array
    {
        $tables = [];

        foreach ($fields as $Field) {
            if ($Field->table !== null && ! in_array($Field->table, $tables, true)) {
                $tables[] = $Field->table;
            }
        }

        return $tables;
    }

    /** @param  list<string>  $imports */
    private function file(array $imports, string $body): string
    {
        return sprintf("<?php\n\nnamespace %s;\n\n%s\n%s", $this->Blueprint->namespace(), $this->imports($imports), $body);
    }

    /** @param  list<string>  $imports */
    private function imports(array $imports): string
    {
        $unique = array_values(array_unique($imports));

        usort($unique, static fn (string $a, string $b): int => strcasecmp($a, $b));

        return implode('', array_map(static fn (string $import): string => 'use '.$import.";\n", $unique));
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
