<?php

use App\Models\CacheEntry;
use App\Models\User;
use App\Modules\Api\Cache\Store\CacheStoreRequest;
use App\Modules\Api\Cache\Store\CacheStoreResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\ApiRoute;
use App\Sources\Db\App\Cache;

// See CacheIndexTest: the first test of a process keeps its writes, so the
// table is cleared rather than assumed empty.
beforeEach(function (): void {
    CacheEntry::query()->delete();
});

test('authenticated user can write a cache entry', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::cache->value, [
                CacheStoreRequest::key => 'greeting',
                CacheStoreRequest::value => 'hello',
                CacheStoreRequest::expiration => 1750000000,
            ])
    )->assertStatus(201)
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(CacheStoreResponse::class),
            ApiResponse::data => [
                CacheStoreResponse::key => 'greeting',
                CacheStoreResponse::value => 'hello',
                CacheStoreResponse::expiration => 1750000000,
            ],
        ]);

    $this->assertDatabaseHas(Cache::table(), [
        Cache::key->value => 'greeting',
        Cache::value->value => 'hello',
        Cache::expiration->value => 1750000000,
    ]);
});

test('writing a key that is already cached replaces it', function (): void {
    $User = User::factory()->createOne();

    CacheEntry::query()->insert([
        Cache::key->value => 'greeting',
        Cache::value->value => 'hello',
        Cache::expiration->value => 100,
    ]);

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::cache->value, [
            CacheStoreRequest::key => 'greeting',
            CacheStoreRequest::value => 'goodbye',
            CacheStoreRequest::expiration => 200,
        ])
        ->assertStatus(201);

    expect(CacheEntry::query()->where(Cache::key->value, 'greeting')->count())->toBe(1);

    $this->assertDatabaseHas(Cache::table(), [
        Cache::key->value => 'greeting',
        Cache::value->value => 'goodbye',
        Cache::expiration->value => 200,
    ]);
});

test('a blank key is rejected', function (): void {
    $User = User::factory()->createOne();

    // Blank is a server policy, not a published constraint: the document admits
    // the empty string, so the request still conforms and the 422 is reachable.
    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->postJson(ApiRoute::cache->value, [
                CacheStoreRequest::key => '',
                CacheStoreRequest::value => 'hello',
                CacheStoreRequest::expiration => 1750000000,
            ])
    )->assertStatus(422)
        ->assertJsonValidationErrors(CacheStoreRequest::key);

    expect(CacheEntry::query()->count())->toBe(0);
});

test('a key longer than the column is rejected', function (): void {
    $User = User::factory()->createOne();

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->postJson(ApiRoute::cache->value, [
            CacheStoreRequest::key => str_repeat('k', 256),
            CacheStoreRequest::value => 'hello',
            CacheStoreRequest::expiration => 1750000000,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(CacheStoreRequest::key);
});

test('an unauthenticated request cannot write a cache entry', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->postJson(ApiRoute::cache->value, [
            CacheStoreRequest::key => 'greeting',
            CacheStoreRequest::value => 'hello',
            CacheStoreRequest::expiration => 1750000000,
        ])
    )->assertStatus(401);
});
