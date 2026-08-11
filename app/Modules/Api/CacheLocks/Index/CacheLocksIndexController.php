<?php

namespace App\Modules\Api\CacheLocks\Index;

use App\Modules\Api\CacheLocks\Show\CacheLocksShowResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Sources\Db\App\CacheLocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksIndexController
{
    /**
     * @throws ReflectionException
     */
    #[ApiSchema(static function (): array {
        return CacheLocksIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Paginator = DB::table(CacheLocks::table())
            ->orderBy(CacheLocks::key->value)
            ->paginate(PaginationParameters::perPage($Request));

        return api_response()->ok(CacheLocksIndexResponse::from([
            CacheLocksIndexResponse::locks => array_map(
                static fn (mixed $Lock): array => CacheLocksShowResponse::from((array) $Lock)->toArray(),
                $Paginator->items(),
            ),
            CacheLocksIndexResponse::pagination => PaginationResponse::of($Paginator)->toArray(),
        ]));
    }
}
