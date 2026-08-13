<?php

use App\Models\CacheLock;
use App\Models\User;
use App\Modules\Api\CacheLocks\Index\CacheLocksIndexResponse;
use App\Modules\Api\CacheLocks\Show\CacheLocksShowResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Routes\ApiRoute;
use App\Sources\Db\App\CacheLocks;

// The endpoints read the whole table, so each test owns it. Rollback cannot be
// relied on for the first test of a process: TestCase::setUp() runs
// migrate:fresh inside the open transaction, and the DDL commits it.
beforeEach(function (): void {
    CacheLock::query()->delete();
});

test('authenticated user can list the cache locks', function (): void {
    $User = User::factory()->createOne();

    CacheLock::query()->insert([
        [CacheLocks::key->value => 'beta', CacheLocks::owner->value => 'worker-2', CacheLocks::expiration->value => 200],
        [CacheLocks::key->value => 'alpha', CacheLocks::owner->value => 'worker-1', CacheLocks::expiration->value => 100],
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)->getJson(ApiRoute::cache_locks->value)
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(CacheLocksIndexResponse::class),
            ApiResponse::data => [
                CacheLocksIndexResponse::locks => [
                    // Ordered by key, so the list is the same on every call.
                    [
                        CacheLocksShowResponse::key => 'alpha',
                        CacheLocksShowResponse::owner => 'worker-1',
                        CacheLocksShowResponse::expiration => 100,
                    ],
                    [
                        CacheLocksShowResponse::key => 'beta',
                        CacheLocksShowResponse::owner => 'worker-2',
                        CacheLocksShowResponse::expiration => 200,
                    ],
                ],
                CacheLocksIndexResponse::pagination => [
                    PaginationResponse::page => 1,
                    PaginationResponse::per_page => PaginationParameters::default_per_page,
                    PaginationResponse::total => 2,
                    PaginationResponse::last_page => 1,
                ],
            ],
        ]);
});

test('an empty table lists no locks', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::cache_locks->value)
        ->assertOk()
        ->assertJsonCount(0, ApiResponse::data.'.'.CacheLocksIndexResponse::locks)
        // An empty table still has a page, so last_page never drops below 1.
        ->assertJsonPath('data.pagination.'.PaginationResponse::last_page, 1);
});

test('the locks are paged', function (): void {
    $User = User::factory()->createOne();

    CacheLock::query()->insert([
        [CacheLocks::key->value => 'alpha', CacheLocks::owner->value => 'a', CacheLocks::expiration->value => 100],
        [CacheLocks::key->value => 'beta', CacheLocks::owner->value => 'b', CacheLocks::expiration->value => 200],
        [CacheLocks::key->value => 'gamma', CacheLocks::owner->value => 'c', CacheLocks::expiration->value => 300],
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->getJson(ApiRoute::cache_locks->value.'?per_page=2&page=2')
    )->assertOk()
        ->assertJsonCount(1, ApiResponse::data.'.'.CacheLocksIndexResponse::locks)
        ->assertJsonPath('data.locks.0.'.CacheLocksShowResponse::key, 'gamma')
        ->assertJsonPath('data.'.CacheLocksIndexResponse::pagination, [
            PaginationResponse::page => 2,
            PaginationResponse::per_page => 2,
            PaginationResponse::total => 3,
            PaginationResponse::last_page => 2,
        ]);
});

test('a page past the last one is empty', function (): void {
    $User = User::factory()->createOne();

    CacheLock::query()->insert([
        CacheLocks::key->value => 'alpha',
        CacheLocks::owner->value => 'a',
        CacheLocks::expiration->value => 100,
    ]);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::cache_locks->value.'?page=9')
        ->assertOk()
        ->assertJsonCount(0, ApiResponse::data.'.'.CacheLocksIndexResponse::locks)
        ->assertJsonPath('data.pagination.'.PaginationResponse::total, 1);
});

test('an unauthenticated request cannot list the cache locks', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::cache_locks->value)
    )->assertStatus(401);
});
