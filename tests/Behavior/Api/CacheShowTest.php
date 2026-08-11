<?php

use App\Models\User;
use App\Modules\Api\Cache\KeyParameter;
use App\Modules\Api\Cache\Show\CacheShowResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use App\Routes\ApiRoute;
use App\Sources\Db\App\Cache;
use Illuminate\Support\Facades\DB;

// See CacheIndexTest: the first test of a process keeps its writes, so the
// table is cleared rather than assumed empty.
beforeEach(function (): void {
    DB::table(Cache::table())->delete();
});

test('authenticated user can retrieve one cache entry', function (): void {
    $User = User::factory()->createOne();

    DB::table(Cache::table())->insert([
        Cache::key->value => 'greeting',
        Cache::value->value => 'hello',
        Cache::expiration->value => 1750000000,
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->getJson(ApiRoute::cache_key->url([KeyParameter::name => 'greeting']))
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(CacheShowResponse::class),
            ApiResponse::data => [
                CacheShowResponse::key => 'greeting',
                CacheShowResponse::value => 'hello',
                CacheShowResponse::expiration => 1750000000,
            ],
        ]);
});

test('a key that is not cached is not found', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->getJson(ApiRoute::cache_key->url([KeyParameter::name => 'absent']))
    )->assertStatus(404)
        ->assertJsonPath(ApiResponse::message, ErrorCode::cache_entry_not_found->value);
});

test('an unauthenticated request cannot retrieve a cache entry', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::cache_key->url([KeyParameter::name => 'greeting']))
    )->assertStatus(401);
});
