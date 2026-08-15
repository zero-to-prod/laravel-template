<?php

namespace App\Modules\Api\Public\Readme;

use App\Helpers\CacheKey;
use App\Modules\Api\Support\PublicApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

readonly class ReadmeController
{
    #[PublicApiSchema(static function (): array {
        return ReadmeSchema::schema();
    })]
    public function __invoke(): JsonResponse
    {
        $content = Cache::get(CacheKey::api_readme->value);

        return api_response()->ok(ReadmeResponse::from([
            ReadmeResponse::content => is_string($content)
                ? $content
                : (string) file_get_contents(resource_path(CacheKey::api_readme->value)),
        ]));
    }
}
