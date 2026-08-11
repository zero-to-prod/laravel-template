<?php

namespace App\Modules\Api\Readme;

use Illuminate\Http\JsonResponse;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class ReadmeController
{
    /** @throws ReflectionException */
    #[ApiSchema(static function (): array {
        return ReadmeSchema::schema();
    })]
    public function __invoke(): JsonResponse
    {
        return api_response()->ok(ReadmeResponse::from([
            ReadmeResponse::content => (string) file_get_contents(resource_path('api-readme.md')),
        ]));
    }
}
