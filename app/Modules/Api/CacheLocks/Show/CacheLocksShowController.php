<?php

namespace App\Modules\Api\CacheLocks\Show;

use App\Modules\Api\CacheLocks\KeyParameter;
use App\Modules\Api\Support\ErrorCode;
use App\Sources\Db\App\CacheLocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

readonly class CacheLocksShowController
{
    /**
     * @throws ReflectionException
     */
    #[ApiSchema(static function (): array {
        return CacheLocksShowSchema::schema();
    })]
    public function __invoke(string $key): JsonResponse
    {
        $Lock = DB::table(CacheLocks::table())->where(CacheLocks::key->value, $key)->first();

        if ($Lock === null) {
            return api_response()->notFound(ErrorCode::cache_lock_not_found, [KeyParameter::name => $key]);
        }

        return api_response()->ok(CacheLocksShowResponse::from((array) $Lock));
    }
}
