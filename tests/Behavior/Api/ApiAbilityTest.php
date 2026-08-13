<?php

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Modules\Api\Cache\Store\CacheStoreRequest;
use App\Modules\Api\CacheLocks\Store\CacheLocksStoreRequest;
use App\Modules\Api\Support\AbilityQuery;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\User\Token\Store\UserTokenStoreRequest;
use App\Modules\Api\User\Update\UserUpdateRequest;
use App\Routes\ApiRoute;

/**
 * A body each write operation would accept, so the refusal is the only thing under test.
 *
 * The document is enforced on the way in as well as out, so a request the document
 * rejects never reaches the guard and proves nothing about it.
 *
 * @return array<string, array<string, mixed>>
 */
function acceptedBodies(): array
{
    return [
        HttpVerb::patch->ability(ApiRoute::user->value) => [UserUpdateRequest::name => 'Renamed'],
        HttpVerb::post->ability(ApiRoute::user_tokens->value) => [UserTokenStoreRequest::name => 'Another'],
        HttpVerb::post->ability(ApiRoute::cache->value) => [
            CacheStoreRequest::key => 'key',
            CacheStoreRequest::value => 'value',
            CacheStoreRequest::expiration => 60,
        ],
        HttpVerb::post->ability(ApiRoute::cache_locks->value) => [
            CacheLocksStoreRequest::key => 'key',
            CacheLocksStoreRequest::owner => 'owner',
            CacheLocksStoreRequest::expiration => 60,
        ],
    ];
}

test('a token granted nothing is refused every method of every endpoint it can be granted', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device', [])->plainTextToken;
    $bodies = acceptedBodies();

    foreach (AbilityQuery::get() as $path => $verbs) {
        foreach ($verbs as $HttpVerb) {
            $ability = $HttpVerb->ability($path);
            $url = (string) preg_replace('/\{[^}]+}/', 'missing', $path);

            $this->assertMatchesSchema(
                $this->withToken($token)->json($HttpVerb->value, $url, $bodies[$ability] ?? [])
            )
                ->assertForbidden()
                ->assertJsonPath(ApiResponse::message, ErrorCode::missing_ability->value)
                ->assertJsonPath(ApiResponse::type, 'error');
        }
    }
});

test('a token granted one verb of one path reaches that and nothing else', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device', [HttpVerb::get->ability(ApiRoute::user->value)])->plainTextToken;

    $this->assertMatchesSchema($this->withToken($token)->getJson(ApiRoute::user->value))->assertOk();
    $this->assertMatchesSchema($this->withToken($token)->getJson(ApiRoute::user_tokens->value))->assertForbidden();
    $this->assertMatchesSchema(
        $this->withToken($token)->patchJson(ApiRoute::user->value, [UserUpdateRequest::name => 'Renamed'])
    )->assertForbidden();

    expect($User->refresh()->name)->not->toBe('Renamed');
});

test('an endpoint reached without a token is not gated by an ability', function (): void {
    $this->assertMatchesSchema($this->getJson(ApiRoute::readme->value))->assertOk();
});
