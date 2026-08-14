<?php

use App\Mcp\Endpoint\EndpointWriter;

test('a route case is inserted before the attributes of the following case', function (): void {
    $Writer = new ReflectionClass(EndpointWriter::class)->newInstanceWithoutConstructor();
    $insert = new ReflectionMethod(EndpointWriter::class, 'insert');
    $contents = <<<'PHP'
        enum ApiRoute: string
        {
            case authenticated = '/api/authenticated';
            #[AdminLink]
            case readme = '/api/readme';
        }
        PHP;

    expect($insert->invoke($Writer, $contents, "    case random = '/api/random';", '    case '))->toContain(<<<'PHP'
            case random = '/api/random';
            #[AdminLink]
            case readme = '/api/readme';
        PHP);
});
