<?php

namespace App\Modules\Api\Cache\Index;

use App\Modules\Api\Cache\Show\CacheShowResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Sources\Db\App\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheIndexController
{
    /**
     * @throws ReflectionException
     */
    #[ApiSchema(static function (): array {
        return CacheIndexSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        // Ordered by key: paging an unordered table can serve the same row
        // twice and skip another.
        $Paginator = DB::table(Cache::table())
            ->orderBy(Cache::key->value)
            ->paginate(PaginationParameters::perPage($Request));

        return api_response()->ok(CacheIndexResponse::from([
            // Through the show response, so the list and the single entry are
            // the same object down to the column the table would have leaked.
            // items() is mixed: the query builder's paginator carries no row
            // type the way an Eloquent one does.
            CacheIndexResponse::entries => array_map(
                static fn (mixed $Entry): array => CacheShowResponse::from((array) $Entry)->toArray(),
                $Paginator->items(),
            ),
            CacheIndexResponse::pagination => PaginationResponse::of($Paginator)->toArray(),
        ]));
    }
}
