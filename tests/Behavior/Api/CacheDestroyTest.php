<?php

use App\Models\User;
use App\Modules\Api\Cache\Destroy\CacheDestroyResponse;
use App\Modules\Api\Cache\KeyParameter;
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

test('authenticated user can delete a cache entry', function (): void {
    $User = User::factory()->createOne();

    DB::table(Cache::table())->insert([
        Cache::key->value => 'greeting',
        Cache::value->value => 'hello',
        Cache::expiration->value => 1750000000,
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->deleteJson(ApiRoute::cache_key->url([KeyParameter::name => 'greeting']))
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::message => class_basename(CacheDestroyResponse::class),
            ApiResponse::type => class_basename(CacheDestroyResponse::class),
        ]);

    $this->assertDatabaseMissing(Cache::table(), [Cache::key->value => 'greeting']);
});

test('deleting one entry leaves the others', function (): void {
    $User = User::factory()->createOne();

    DB::table(Cache::table())->insert([
        [Cache::key->value => 'kept', Cache::value->value => 'a', Cache::expiration->value => 100],
        [Cache::key->value => 'dropped', Cache::value->value => 'b', Cache::expiration->value => 200],
    ]);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->deleteJson(ApiRoute::cache_key->url([KeyParameter::name => 'dropped']))
        ->assertOk();

    $this->assertDatabaseMissing(Cache::table(), [Cache::key->value => 'dropped']);
    $this->assertDatabaseHas(Cache::table(), [Cache::key->value => 'kept']);
});

test('a key that is not cached is not found', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->deleteJson(ApiRoute::cache_key->url([KeyParameter::name => 'absent']))
    )->assertStatus(404)
        ->assertJsonPath(ApiResponse::message, ErrorCode::cache_entry_not_found->value);
});

test('an unauthenticated request cannot delete a cache entry', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->deleteJson(ApiRoute::cache_key->url([KeyParameter::name => 'greeting']))
    )->assertStatus(401);
});
