<?php

namespace App\Modules\Llms;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

readonly class LlmsController
{
    public function __invoke(): Response
    {
        return new Response(
            (string) file_get_contents(resource_path('llms.txt')),
            ResponseAlias::HTTP_OK,
            ['Content-Type' => 'text/markdown; charset=utf-8'],
        );
    }
}
