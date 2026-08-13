<?php

use App\Models\User;
use App\Modules\Api\Support\ApiResponse;
use App\Modules\Api\User\Update\UserUpdateRequest;
use App\Modules\Api\User\Update\UserUpdateResponse;
use App\Routes\ApiRoute;
use App\Sources\Db\App\Users;

test('authenticated user can update their name', function (): void {
    $User = User::factory()->createOne();

    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->patchJson(ApiRoute::user->value, [UserUpdateRequest::name => 'Zero To Prod'])
    )->assertOk()
        ->assertJson([
            ApiResponse::success => true,
            ApiResponse::type => class_basename(UserUpdateResponse::class),
            ApiResponse::data => [
                UserUpdateResponse::id => $User->id,
                UserUpdateResponse::name => 'Zero To Prod',
            ],
        ]);

    $this->assertDatabaseHas(Users::table(), [
        Users::id->value => $User->id,
        Users::name->value => 'Zero To Prod',
    ]);
});

test('a blank name is rejected', function (): void {
    $User = User::factory()->createOne();

    // Blank is a server policy, not a published constraint: the document admits
    // the empty string, so the request still conforms and the 422 is reachable.
    $this->assertMatchesSchema(
        $this->withToken($User->createToken('test-device')->plainTextToken)
            ->patchJson(ApiRoute::user->value, [UserUpdateRequest::name => ''])
    )->assertStatus(422)
        ->assertJsonValidationErrors(UserUpdateRequest::name);

    expect($User->refresh()->name)->not->toBeEmpty();
});

test('unauthenticated user cannot update the name', function (): void {
    $this->assertMatchesSchema(
        $this->withToken('invalid-token')
            ->patchJson(ApiRoute::user->value, [UserUpdateRequest::name => 'Zero To Prod'])
    )->assertStatus(401);
});
