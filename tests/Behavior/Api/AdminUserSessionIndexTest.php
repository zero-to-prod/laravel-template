<?php

use App\Models\Session;
use App\Models\User;
use App\Modules\Api\Admin\User\Session\Index\AdminUserSessionIndexResponse;
use App\Modules\Api\Support\ApiResponse;
use App\Routes\Admin;
use App\Sources\Db\App\Sessions;

test('list a user sessions', function (): void {
    $User = User::factory()->createOne();
    Session::query()->create([
        Sessions::id->value => 'managed-session',
        Sessions::user_id->value => $User->id,
        Sessions::ip_address->value => '127.0.0.1',
        Sessions::user_agent->value => 'Example Browser',
        Sessions::payload->value => 'private payload',
        Sessions::last_activity->value => now()->timestamp,
    ]);

    $Response = $this->actingAs(adminUser())->getJson(Admin::api_user_sessions->url([
        Admin::userParameter => $User->id,
    ]));

    $this->assertMatchesSchema($Response)
        ->assertOk()
        ->assertJsonPath(ApiResponse::type, class_basename(AdminUserSessionIndexResponse::class))
        ->assertJsonPath('data.sessions.0.id', 'managed-session')
        ->assertJsonMissing(['payload' => 'private payload']);
});

test('session list rejects guests and missing users', function (): void {
    $url = Admin::api_user_sessions->url([Admin::userParameter => 'missing']);

    $this->assertMatchesSchema($this->getJson($url))->assertUnauthorized();
    $this->actingAs(adminUser())->getJson($url)->assertNotFound();
});
