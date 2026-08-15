<?php

namespace App\Modules\Api\Public\Readme;

use App\Modules\Api\Support\PublicApiSchema;
use Illuminate\Http\JsonResponse;

readonly class ReadmeController
{
    #[PublicApiSchema(static function (): array {
        return ReadmeSchema::schema();
    })]
    public function __invoke(): JsonResponse
    {
        return api_response()->ok(ReadmeResponse::from([
            ReadmeResponse::content => (string) file_get_contents(resource_path('api-readme.md')),
        ]));
    }
}
