<?php

use App\Models\CacheLock;
use App\Models\User;
use App\Modules\Api\CacheLocks\Destroy\CacheLocksDestroyResponse;
use App\Modules\Api\CacheLocks\KeyParameter;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use App\Routes\ApiRoute;
use App\Sources\Db\App\CacheLocks;

// See CacheLocksIndexTest: the first test of a process keeps its writes, so the
// table is cleared rather than assumed empty.
beforeEach(function (): void {
    CacheLock::query()->delete();
});

test('authenticated user can delete a cache lock', function (): void {
    $User = User::factory()->createOne();

    CacheLock::query()->insert([
        CacheLocks::key->value => 'deploy',
        CacheLocks::owner->value => 'worker-1',
        CacheLocks::expiration->value => 1750000000,
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->deleteJson(ApiRoute::cache_locks_key->url([KeyParameter::name => 'deploy']))
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::message => class_basename(CacheLocksDestroyResponse::class),
            ApiResponse::type => class_basename(CacheLocksDestroyResponse::class),
        ]);

    $this->assertDatabaseMissing(CacheLocks::table(), [CacheLocks::key->value => 'deploy']);
});

test('deleting one lock leaves the others', function (): void {
    $User = User::factory()->createOne();

    CacheLock::query()->insert([
        [CacheLocks::key->value => 'kept', CacheLocks::owner->value => 'a', CacheLocks::expiration->value => 100],
        [CacheLocks::key->value => 'dropped', CacheLocks::owner->value => 'b', CacheLocks::expiration->value => 200],
    ]);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->deleteJson(ApiRoute::cache_locks_key->url([KeyParameter::name => 'dropped']))
        ->assertOk();

    $this->assertDatabaseMissing(CacheLocks::table(), [CacheLocks::key->value => 'dropped']);
    $this->assertDatabaseHas(CacheLocks::table(), [CacheLocks::key->value => 'kept']);
});

test('a key that is not locked is not found', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->deleteJson(ApiRoute::cache_locks_key->url([KeyParameter::name => 'absent']))
    )->assertStatus(404)
        ->assertJsonPath(ApiResponse::message, ErrorCode::cache_lock_not_found->value);
});

test('an unauthenticated request cannot delete a cache lock', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->deleteJson(ApiRoute::cache_locks_key->url([KeyParameter::name => 'deploy']))
    )->assertStatus(401);
});
