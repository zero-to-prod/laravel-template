<?php

use App\Models\CacheEntry;
use App\Models\User;
use App\Modules\Api\Cache\Index\CacheIndexResponse;
use App\Modules\Api\Cache\Show\CacheShowResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use App\Routes\ApiRoute;
use App\Sources\Db\App\Cache;

// The endpoints read the whole table, so each test owns it. Rollback cannot be
// relied on for the first test of a process: TestCase::setUp() runs
// migrate:fresh inside the open transaction, and the DDL commits it.
beforeEach(function (): void {
    CacheEntry::query()->delete();
});

test('authenticated user can list the cache entries', function (): void {
    $User = User::factory()->createOne();

    CacheEntry::query()->insert([
        [Cache::key->value => 'beta', Cache::value->value => 'second', Cache::expiration->value => 200],
        [Cache::key->value => 'alpha', Cache::value->value => 'first', Cache::expiration->value => 100],
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)->getJson(ApiRoute::cache->value)
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(CacheIndexResponse::class),
            ApiResponse::data => [
                CacheIndexResponse::entries => [
                    // Ordered by key, so the list is the same on every call.
                    [
                        CacheShowResponse::key => 'alpha',
                        CacheShowResponse::value => 'first',
                        CacheShowResponse::expiration => 100,
                    ],
                    [
                        CacheShowResponse::key => 'beta',
                        CacheShowResponse::value => 'second',
                        CacheShowResponse::expiration => 200,
                    ],
                ],
                CacheIndexResponse::pagination => [
                    PaginationResponse::page => 1,
                    PaginationResponse::per_page => PaginationParameters::default_per_page,
                    PaginationResponse::total => 2,
                    PaginationResponse::last_page => 1,
                ],
            ],
        ]);
});

test('an empty cache lists no entries', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::cache->value)
        ->assertOk()
        ->assertJsonCount(0, ApiResponse::data.'.'.CacheIndexResponse::entries)
        // An empty table still has a page, so last_page never drops below 1.
        ->assertJsonPath('data.pagination.'.PaginationResponse::last_page, 1);
});

test('the entries are paged', function (): void {
    $User = User::factory()->createOne();

    CacheEntry::query()->insert([
        [Cache::key->value => 'alpha', Cache::value->value => 'a', Cache::expiration->value => 100],
        [Cache::key->value => 'beta', Cache::value->value => 'b', Cache::expiration->value => 200],
        [Cache::key->value => 'gamma', Cache::value->value => 'c', Cache::expiration->value => 300],
    ]);

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->getJson(ApiRoute::cache->value.'?per_page=2&page=2')
    )->assertOk()
        ->assertJsonCount(1, ApiResponse::data.'.'.CacheIndexResponse::entries)
        ->assertJsonPath('data.entries.0.'.CacheShowResponse::key, 'gamma')
        ->assertJsonPath('data.'.CacheIndexResponse::pagination, [
            PaginationResponse::page => 2,
            PaginationResponse::per_page => 2,
            PaginationResponse::total => 3,
            PaginationResponse::last_page => 2,
        ]);
});

test('a page past the last one is empty', function (): void {
    $User = User::factory()->createOne();

    CacheEntry::query()->insert([
        Cache::key->value => 'alpha',
        Cache::value->value => 'a',
        Cache::expiration->value => 100,
    ]);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::cache->value.'?page=9')
        ->assertOk()
        ->assertJsonCount(0, ApiResponse::data.'.'.CacheIndexResponse::entries)
        ->assertJsonPath('data.pagination.'.PaginationResponse::total, 1);
});

test('an unauthenticated request cannot list the cache entries', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::cache->value)
    )->assertStatus(401);
});
