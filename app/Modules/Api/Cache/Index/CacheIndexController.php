<?php

namespace App\Modules\Api\Cache\Index;

use App\Models\CacheEntry;
use App\Modules\Api\Cache\Show\CacheShowResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Sources\Db\App\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheIndexController
{
    #[ApiSchema(static function (): array {
        return CacheIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Builder = CacheEntry::query();
        $Builder->orderBy(Cache::key->value);

        $Paginator = $Builder->paginate(PaginationParameters::perPage($Request));

        return api_response()->ok(CacheIndexResponse::from([
            CacheIndexResponse::entries => $Paginator->getCollection()
                ->map(static fn (CacheEntry $CacheEntry): array => CacheShowResponse::from($CacheEntry->toArray())->toArray())
                ->all(),
            CacheIndexResponse::pagination => PaginationResponse::of($Paginator)->toArray(),
        ]));
    }
}
