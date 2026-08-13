<?php

namespace App\Modules\Api\CacheLocks\Index;

use App\Models\CacheLock;
use App\Modules\Api\CacheLocks\Show\CacheLocksShowResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Sources\Db\App\CacheLocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksIndexController
{
    #[ApiSchema(static function (): array {
        return CacheLocksIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Builder = CacheLock::query();
        $Builder->orderBy(CacheLocks::key->value);

        $Paginator = $Builder->paginate(PaginationParameters::perPage($Request));

        return api_response()->ok(CacheLocksIndexResponse::from([
            CacheLocksIndexResponse::locks => $Paginator->getCollection()
                ->map(static fn (CacheLock $CacheLock): array => CacheLocksShowResponse::from($CacheLock->toArray())->toArray())
                ->all(),
            CacheLocksIndexResponse::pagination => PaginationResponse::of($Paginator)->toArray(),
        ]));
    }
}
