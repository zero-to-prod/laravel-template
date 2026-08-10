<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use App\Modules\Api\User\Token\Show\UserTokenShowResponse;
use App\Modules\Api\User\Token\TokenParameter;
use App\Routes\ApiRoute;

test('authenticated user can retrieve one of their tokens', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device')->plainTextToken;
    $Subject = issuedToken($User, $User->createToken('subject-device', ['tokens:read']));

    $this->assertMatchesSchema(
        $this->withToken($token)->getJson(ApiRoute::user_token->url([TokenParameter::name => $Subject->id]))
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(UserTokenShowResponse::class),
            ApiResponse::data => [
                UserTokenShowResponse::id => $Subject->id,
                UserTokenShowResponse::name => 'subject-device',
                UserTokenShowResponse::abilities => ['tokens:read'],
                UserTokenShowResponse::last_used_at => null,
                UserTokenShowResponse::expires_at => null,
                // The model serializes its dates, so the response carries
                // whatever Model::serializeDate() published.
                UserTokenShowResponse::created_at => $Subject->toArray()[UserTokenShowResponse::created_at],
                UserTokenShowResponse::updated_at => $Subject->toArray()[UserTokenShowResponse::updated_at],
            ],
        ]);
});

test('the stored digest is never exposed', function (): void {
    $User = User::factory()->createOne();
    $Subject = issuedToken($User, $User->createToken('subject-device'));

    $this->withToken($User->createToken('test-device')->plainTextToken)
        ->getJson(ApiRoute::user_token->url([TokenParameter::name => $Subject->id]))
        ->assertOk()
        ->assertJsonMissingPath('data.token')
        ->assertJsonMissingPath('data.tokenable_id');
});

test('a token belonging to somebody else is not found', function (): void {
    $User = User::factory()->createOne();
    $Other = User::factory()->createOne();
    $Theirs = issuedToken($Other, $Other->createToken('somebody-elses'));

    // 404 rather than 403: a 403 would confirm the id names a real token.
    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->getJson(ApiRoute::user_token->url([TokenParameter::name => $Theirs->id]))
    )->assertStatus(404)
        ->assertJsonPath(ApiResponse::message, ErrorCode::token_not_found->value);
});

test('unauthenticated user cannot retrieve a token', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')->getJson(ApiRoute::user_token->url([TokenParameter::name => 1]))
    )->assertStatus(401);
});
