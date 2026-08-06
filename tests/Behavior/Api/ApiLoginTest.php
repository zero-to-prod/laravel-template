<?php

use App\DataModels\User;
use App\Models\User as ModelUser;
use App\Modules\Api\Requests\ApiLoginRequest;
use App\Routes\ApiRoute;

test('login with valid credentials', function (): void {
    $User = ModelUser::factory([User::password => User::password])->createOne();
    $payload = ApiLoginRequest::from([
        ApiLoginRequest::email => $User->email,
        ApiLoginRequest::password => User::password,
        ApiLoginRequest::device_name => 'test-device',
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
        ApiLoginRequest::email => 'invalid-email',
        ApiLoginRequest::password => 'password',
        ApiLoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(ApiLoginRequest::email);
});

test('validation fails with invalid password', function (): void {
    $payload = [
        ApiLoginRequest::email => 'test@example.com',
        ApiLoginRequest::password => '',
        ApiLoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(ApiLoginRequest::password);
});

test('validation fails with missing device name', function (): void {
    $User = ModelUser::factory()->createOne();
    $payload = [
        ApiLoginRequest::email => $User->email,
        ApiLoginRequest::password => 'password',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(ApiLoginRequest::device_name);
});

test('login fails with invalid credentials', function (): void {
    $User = ModelUser::factory()->createOne();
    $payload = [
        ApiLoginRequest::email => $User->email,
        ApiLoginRequest::password => 'wrong-password',
        ApiLoginRequest::device_name => 'test-device',
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
        ApiLoginRequest::email => 'nonexistent@example.com',
        ApiLoginRequest::password => 'password',
        ApiLoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'invalid_credentials',
        ]);
});

test('validation fails with missing required fields', function (): void {
    $this->assertMatchesSchema($this->postJson(ApiRoute::login->value, []))
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            ApiLoginRequest::email,
            ApiLoginRequest::password,
            ApiLoginRequest::device_name,
        ]);
});

test('input is sanitized during login', function (): void {
    ModelUser::factory()->createOne([
        User::email => 'test@example.com',
    ]);

    $payload = [
        ApiLoginRequest::email => ' TEST@EXAMPLE.COM ',
        ApiLoginRequest::password => User::password,
        ApiLoginRequest::device_name => 'test-device',
    ];

    $this->postJson(ApiRoute::login->value, $payload)
        ->assertOk()
        ->assertJsonStructure(['data' => ['token']]);
});

test('token is created with correct device name', function (): void {
    $User = ModelUser::factory([User::password => User::password])->createOne();
    $deviceName = 'test-device-name';

    $payload = [
        ApiLoginRequest::email => $User->email,
        ApiLoginRequest::password => User::password,
        ApiLoginRequest::device_name => $deviceName,
    ];

    $this->postJson(ApiRoute::login->value, $payload)->assertOk();

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => $deviceName,
        'tokenable_id' => $User->id,
    ]);
});
