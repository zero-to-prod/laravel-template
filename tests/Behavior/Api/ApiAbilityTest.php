<?php

use App\Helpers\HttpVerb;
use App\Models\User;
use App\Modules\Api\Support\AbilityQuery;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\Support\ErrorCode;
use App\Routes\ApiRoute;

test('a token granted nothing is refused every method of every endpoint it can be granted', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device', [])->plainTextToken;

    foreach (AbilityQuery::get() as $path => $verbs) {
        foreach ($verbs as $HttpVerb) {
            $url = (string) preg_replace('/\{[^}]+}/', 'missing', $path);

            $this->assertMatchesSchema($this->withToken($token)->json($HttpVerb->value, $url))
                ->assertForbidden()
                ->assertJsonPath(ApiResponse::message, ErrorCode::missing_ability->value)
                ->assertJsonPath(ApiResponse::type, 'error');
        }
    }
});

test('a token granted one verb of one path reaches that', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device', [HttpVerb::get->ability(ApiRoute::user->value)])->plainTextToken;

    $this->assertMatchesSchema($this->withToken($token)->getJson(ApiRoute::user->value))->assertOk();
});

test('an ability granted for another verb of the same path does not open this one', function (): void {
    $User = User::factory()->createOne();
    $token = $User->createToken('test-device', [HttpVerb::delete->ability(ApiRoute::user->value)])->plainTextToken;

    $this->assertMatchesSchema($this->withToken($token)->getJson(ApiRoute::user->value))->assertForbidden();
});

test('an endpoint reached without a token is not gated by an ability', function (): void {
    $this->assertMatchesSchema($this->getJson(ApiRoute::readme->value))->assertOk();
});
