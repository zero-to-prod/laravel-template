<?php

namespace Tests\Behavior\Api;

use App\Models\User;
use App\Modules\Api\Requests\ApiLoginRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    #[Test]
    public function login_with_valid_credentials(): void
    {
        $User = User::factory([User::password => User::password])->create();
        $payload = ApiLoginRequest::from([
            ApiLoginRequest::email => $User->email,
            ApiLoginRequest::password => User::password,
            ApiLoginRequest::device_name => 'test-device',
        ]);

        $response = $this->postJson(api()->login, $payload->toArray());

        $response->assertOk()
            ->assertJsonStructure(['success', 'message', 'data' => ['token']])
            ->assertJson(['success' => true]);
    }

    #[Test]
    public function validation_fails_with_invalid_email(): void
    {
        $payload = [
            ApiLoginRequest::email => 'invalid-email',
            ApiLoginRequest::password => 'password',
            ApiLoginRequest::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(ApiLoginRequest::email);
    }

    #[Test]
    public function validation_fails_with_invalid_password(): void
    {
        $payload = [
            ApiLoginRequest::email => 'test@example.com',
            ApiLoginRequest::password => '',
            ApiLoginRequest::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(ApiLoginRequest::password);
    }

    #[Test]
    public function validation_fails_with_missing_device_name(): void
    {
        $User = User::factory()->create();
        $payload = [
            ApiLoginRequest::email => $User->email,
            ApiLoginRequest::password => 'password',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(ApiLoginRequest::device_name);
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $User = User::factory()->create();
        $payload = [
            ApiLoginRequest::email => $User->email,
            ApiLoginRequest::password => 'wrong-password',
            ApiLoginRequest::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'invalid_credentials',
            ]);
    }

    #[Test]
    public function login_fails_with_non_existent_user(): void
    {
        $payload = [
            ApiLoginRequest::email => 'nonexistent@example.com',
            ApiLoginRequest::password => 'password',
            ApiLoginRequest::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'invalid_credentials',
            ]);
    }

    #[Test]
    public function validation_fails_with_missing_required_fields(): void
    {
        $this->postJson(api()->login, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                ApiLoginRequest::email,
                ApiLoginRequest::password,
                ApiLoginRequest::device_name,
            ]);
    }

    #[Test]
    public function input_is_sanitized_during_login(): void
    {
        $User = User::factory()->create([
            User::email => 'test@example.com',
        ]);

        $payload = [
            ApiLoginRequest::email => ' TEST@EXAMPLE.COM ',
            ApiLoginRequest::password => User::password,
            ApiLoginRequest::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertOk()
            ->assertJsonStructure(['data' => ['token']]);
    }

    #[Test]
    public function token_is_created_with_correct_device_name(): void
    {
        $User = User::factory([User::password => User::password])->create();
        $deviceName = 'test-device-name';

        $payload = [
            ApiLoginRequest::email => $User->email,
            ApiLoginRequest::password => User::password,
            ApiLoginRequest::device_name => $deviceName,
        ];

        $this->postJson(api()->login, $payload)->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => $deviceName,
            'tokenable_id' => $User->id,
        ]);
    }
}
