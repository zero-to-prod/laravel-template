<?php

namespace App\Mcp\Tools;

use App\Mcp\OpenApi\OpenApiEndpointMapper;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Override;

#[IsIdempotent]
class ScaffoldOpenApi extends Tool
{
    protected string $name = 'scaffold-openapi';
    protected string $description = 'Generates endpoint modules for every supported operation in an OpenAPI 3.x JSON or YAML schema.';

    /** @return array<string, mixed> */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'openapi' => $schema->string()->description('The complete OpenAPI 3.x document, as JSON or YAML.')->required(),
            'dry_run' => $schema->boolean()->description('Return what would be written without writing it.'),
            'force' => $schema->boolean()->description('Overwrite endpoint files that are already there.'),
        ];
    }

    public function handle(Request $Request, OpenApiEndpointMapper $OpenApiEndpointMapper, ScaffoldEndpoint $ScaffoldEndpoint): Response
    {
        $input = $Request->validate([
            'openapi' => ['required', 'string'],
            'dry_run' => ['boolean'],
            'force' => ['boolean'],
        ]);

        $openapi = $input['openapi'];

        if (! is_string($openapi)) {
            return Response::error('The OpenAPI schema must be a string.');
        }

        try {
            $endpoints = $OpenApiEndpointMapper->map($openapi);
        } catch (InvalidArgumentException $Exception) {
            return Response::error($Exception->getMessage());
        }

        $reports = [];

        foreach ($endpoints as $endpoint) {
            $Response = $ScaffoldEndpoint->scaffold([
                ...$endpoint,
                'dry_run' => ($input['dry_run'] ?? false) === true,
                'force' => ($input['force'] ?? false) === true,
            ]);

            if ($Response->isError()) {
                return $Response;
            }

            $reports[] = (string) $Response->content();
        }

        return Response::text(implode("\n", $reports));
    }
}
