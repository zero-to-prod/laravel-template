<?php

use App\Models\User;
use App\Modules\Api\Login\LoginRequest;
use App\Routes\ApiRoute;
use App\Sources\Db\App\Users;

test('login with valid credentials', function (): void {
    $User = User::factory([Users::password->value => Users::password->value])->createOne();
    $payload = LoginRequest::from([
        LoginRequest::email => $User->email,
        LoginRequest::password => Users::password->value,
        LoginRequest::device_name => 'test-device',
    ]);

    $response = $this->assertMatchesSchema(
        $this->postJson(ApiRoute::login->value, $payload->toArray())
    );

    $response->assertOk()
        ->assertJsonStructure(['success', 'data' => ['token']])
        ->assertJson(['success' => true]);
});

test('validation fails with invalid email', function (): void {
    $payload = [
        LoginRequest::email => 'invalid-email',
        LoginRequest::password => 'password',
        LoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(LoginRequest::email);
});

test('validation fails with invalid password', function (): void {
    $payload = [
        LoginRequest::email => 'test@example.com',
        LoginRequest::password => '',
        LoginRequest::device_name => 'test-device',
    ];

    // The only 422 whose request still conforms to the document: blank is a
    // server policy (NotBlank), not a published constraint. Every other 422
    // case sends a body the document rejects, so the response validator would
    // fail on the request before it ever looked at the response.
    $this->assertMatchesSchema($this->postJson(ApiRoute::login->value, $payload))
        ->assertStatus(422)
        ->assertJsonValidationErrors(LoginRequest::password);
});

test('validation fails with missing device name', function (): void {
    $User = User::factory()->createOne();
    $payload = [
        LoginRequest::email => $User->email,
        LoginRequest::password => 'password',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(LoginRequest::device_name);
});

test('login fails with invalid credentials', function (): void {
    $User = User::factory()->createOne();
    $payload = [
        LoginRequest::email => $User->email,
        LoginRequest::password => 'wrong-password',
        LoginRequest::device_name => 'test-device',
    ];

    $this->assertMatchesSchema($this->postJson(ApiRoute::login->value, $payload))
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'invalid_credentials',
        ]);
});

test('login fails with non existent user', function (): void {
    $payload = [
        LoginRequest::email => 'nonexistent@example.com',
        LoginRequest::password => 'password',
        LoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'invalid_credentials',
        ]);
});

test('validation fails with missing required fields', function (): void {
    $this->postJson(ApiRoute::login->value, [])
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            LoginRequest::email,
            LoginRequest::password,
            LoginRequest::device_name,
        ]);
});

test('input is sanitized during login', function (): void {
    User::factory()->createOne([
        Users::email->value => 'test@example.com',
    ]);

    $payload = [
        LoginRequest::email => ' TEST@EXAMPLE.COM ',
        LoginRequest::password => Users::password->value,
        LoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertOk()
        ->assertJsonStructure(['data' => ['token']]);
});

test('token is created with correct device name', function (): void {
    $User = User::factory([Users::password->value => Users::password->value])->createOne();
    $deviceName = 'test-device-name';

    $payload = [
        LoginRequest::email => $User->email,
        LoginRequest::password => Users::password->value,
        LoginRequest::device_name => $deviceName,
    ];

    $this->postJson(ApiRoute::login->value, $payload)->assertOk();

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => $deviceName,
        'tokenable_id' => $User->id,
    ]);
});
