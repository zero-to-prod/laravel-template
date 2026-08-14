<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ScaffoldEndpoint;
use App\Mcp\Tools\ScaffoldOpenApi;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Tool;

class TemplateServer extends Server
{
    protected string $name = 'Laravel Template';
    protected string $version = '0.1.0';
    protected string $instructions = <<<'MARKDOWN'
        This application's own tools. The zero-to-prod servers document the packages;
        this one writes code against the conventions this application keeps.

        - `scaffold-endpoint` — the artifacts of one API endpoint module
        - `scaffold-openapi` — endpoint modules for the operations in an OpenAPI 3.x document
        MARKDOWN;

    /** @var array<int, class-string<Tool>> */
    protected array $tools = [
        ScaffoldEndpoint::class,
        ScaffoldOpenApi::class,
    ];
}
