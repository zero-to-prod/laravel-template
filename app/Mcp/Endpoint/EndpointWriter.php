<?php

namespace App\Mcp\Endpoint;

use App\Routes\ApiRoute;
use Illuminate\Support\Facades\File;

readonly class EndpointWriter
{
    public function __construct(private EndpointBlueprint $Blueprint, private EndpointRenderer $Renderer) {}

    /** @return array<string, string> */
    public function files(): array
    {
        return $this->Renderer->files();
    }

    /** @return array<string, string> */
    public function parameters(): array
    {
        return array_filter(
            $this->Renderer->parameters(),
            static fn (string $path): bool => ! File::exists(base_path($path)),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /** @return array<string, string> */
    public function edits(): array
    {
        $edits = [];

        $route = $this->apiRoute();

        if ($route !== null) {
            $edits['app/Routes/ApiRoute.php'] = $route;
        }

        $edits[$this->Blueprint->routesFile()] = $this->registration();

        return $edits;
    }

    /** @return list<string> */
    public function collisions(): array
    {
        $collisions = [];

        foreach (array_keys($this->files()) as $path) {
            if (File::exists(base_path($path))) {
                $collisions[] = $path;
            }
        }

        return $collisions;
    }

    public function write(): void
    {
        foreach ([...$this->files(), ...$this->parameters(), ...$this->edits()] as $path => $contents) {
            $absolute = base_path($path);

            File::ensureDirectoryExists(dirname($absolute));
            File::put($absolute, $contents);
        }
    }

    private function apiRoute(): ?string
    {
        $contents = File::get(base_path('app/Routes/ApiRoute.php'));
        $suffix = substr($this->Blueprint->path, strlen(ApiRoute::prefix));

        if (str_contains($contents, sprintf("= self::prefix.'%s';", $suffix))) {
            return null;
        }

        return $this->insert($contents, $this->Blueprint->routeCaseLine(), '    case ');
    }

    private function registration(): string
    {
        $contents = $this->Blueprint->routesFile()
                |> base_path(...)
                |> File::get(...);
        $controller = $this->Blueprint->namespace().'\\'.$this->Blueprint->controllerClass();

        $contents = $this->insert($contents, 'use '.$controller.';', 'use ');

        return rtrim($contents, "\n")."\n".sprintf(
            "Route::%s(ApiRoute::%s->value, %s::class);\n",
            $this->Blueprint->method,
            $this->Blueprint->routeCase,
            $this->Blueprint->controllerClass(),
        );
    }

    private function insert(string $contents, string $line, string $prefix): string
    {
        $lines = explode("\n", $contents);

        if (in_array($line, $lines, true)) {
            return $contents;
        }

        $last = null;

        foreach ($lines as $index => $existing) {
            if (! str_starts_with($existing, $prefix)) {
                continue;
            }

            $last = $index;

            if (strcasecmp($existing, $line) > 0) {
                array_splice($lines, $index, 0, [$line]);

                return implode("\n", $lines);
            }
        }

        if ($last === null) {
            return $contents;
        }

        array_splice($lines, $last + 1, 0, [$line]);

        return implode("\n", $lines);
    }
}
