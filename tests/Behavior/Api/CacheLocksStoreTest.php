<?php

use App\Models\User;
use App\Modules\Api\CacheLocks\Store\CacheLocksStoreRequest;
use App\Modules\Api\CacheLocks\Store\CacheLocksStoreResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\ApiRoute;
use App\Sources\Db\App\CacheLocks;
use Illuminate\Support\Facades\DB;

// See CacheLocksIndexTest: the first test of a process keeps its writes, so the
// table is cleared rather than assumed empty.
beforeEach(function (): void {
    DB::table(CacheLocks::table())->delete();
});

test('authenticated user can write a cache lock', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::cache_locks->value, [
                CacheLocksStoreRequest::key => 'deploy',
                CacheLocksStoreRequest::owner => 'worker-1',
                CacheLocksStoreRequest::expiration => 1750000000,
            ])
    )->assertStatus(201)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(CacheLocksStoreResponse::class),
            ApiResponse::data => [
                CacheLocksStoreResponse::key => 'deploy',
                CacheLocksStoreResponse::owner => 'worker-1',
                CacheLocksStoreResponse::expiration => 1750000000,
            ],
        ]);

    $this->assertDatabaseHas(CacheLocks::table(), [
        CacheLocks::key->value => 'deploy',
        CacheLocks::owner->value => 'worker-1',
        CacheLocks::expiration->value => 1750000000,
    ]);
});

test('writing a key that is already locked replaces it', function (): void {
    $User = User::factory()->createOne();

    DB::table(CacheLocks::table())->insert([
        CacheLocks::key->value => 'deploy',
        CacheLocks::owner->value => 'worker-1',
        CacheLocks::expiration->value => 100,
    ]);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::cache_locks->value, [
            CacheLocksStoreRequest::key => 'deploy',
            CacheLocksStoreRequest::owner => 'worker-2',
            CacheLocksStoreRequest::expiration => 200,
        ])
        ->assertStatus(201);

    expect(DB::table(CacheLocks::table())->where(CacheLocks::key->value, 'deploy')->count())->toBe(1);

    $this->assertDatabaseHas(CacheLocks::table(), [
        CacheLocks::key->value => 'deploy',
        CacheLocks::owner->value => 'worker-2',
        CacheLocks::expiration->value => 200,
    ]);
});

test('a blank key is rejected', function (): void {
    $User = User::factory()->createOne();

    // Blank is a server policy, not a published constraint: the document admits
    // the empty string, so the request still conforms and the 422 is reachable.
    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::cache_locks->value, [
                CacheLocksStoreRequest::key => '',
                CacheLocksStoreRequest::owner => 'worker-1',
                CacheLocksStoreRequest::expiration => 1750000000,
            ])
    )->assertStatus(422)
        ->assertJsonValidationErrors(CacheLocksStoreRequest::key);

    expect(DB::table(CacheLocks::table())->count())->toBe(0);
});

test('a key longer than the column is rejected', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::cache_locks->value, [
            CacheLocksStoreRequest::key => str_repeat('k', 256),
            CacheLocksStoreRequest::owner => 'worker-1',
            CacheLocksStoreRequest::expiration => 1750000000,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(CacheLocksStoreRequest::key);
});

test('an unauthenticated request cannot write a cache lock', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->postJson(ApiRoute::cache_locks->value, [
            CacheLocksStoreRequest::key => 'deploy',
            CacheLocksStoreRequest::owner => 'worker-1',
            CacheLocksStoreRequest::expiration => 1750000000,
        ])
    )->assertStatus(401);
});
