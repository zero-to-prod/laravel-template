<?php

namespace Tests\Behavior\Api;

use App\Models\User;
use App\Modules\Api\Login\ApiLoginForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_with_valid_credentials(): void
    {
        $User = User::factory([User::password => User::password])->create();
        $payload = ApiLoginForm::from([
            ApiLoginForm::email => $User->email,
            ApiLoginForm::password => User::password,
            ApiLoginForm::device_name => 'test-device',
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
            ApiLoginForm::email => 'invalid-email',
            ApiLoginForm::password => 'password',
            ApiLoginForm::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(ApiLoginForm::email);
    }

    #[Test]
    public function validation_fails_with_invalid_password(): void
    {
        $payload = [
            ApiLoginForm::email => 'test@example.com',
            ApiLoginForm::password => '',
            ApiLoginForm::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(ApiLoginForm::password);
    }

    #[Test]
    public function validation_fails_with_missing_device_name(): void
    {
        $User = User::factory()->create();
        $payload = [
            ApiLoginForm::email => $User->email,
            ApiLoginForm::password => 'password',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(ApiLoginForm::device_name);
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $User = User::factory()->create();
        $payload = [
            ApiLoginForm::email => $User->email,
            ApiLoginForm::password => 'wrong-password',
            ApiLoginForm::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    #[Test]
    public function login_fails_with_non_existent_user(): void
    {
        $payload = [
            ApiLoginForm::email => 'nonexistent@example.com',
            ApiLoginForm::password => 'password',
            ApiLoginForm::device_name => 'test-device',
        ];

        $this->postJson(api()->login, $payload)
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    #[Test]
    public function validation_fails_with_missing_required_fields(): void
    {
        $this->postJson(api()->login, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                ApiLoginForm::email,
                ApiLoginForm::password,
                ApiLoginForm::device_name,
            ]);
    }

    #[Test]
    public function input_is_sanitized_during_login(): void
    {
        $User = User::factory()->create([
            User::email => 'test@example.com'
        ]);

        $payload = [
            ApiLoginForm::email => ' TEST@EXAMPLE.COM ',
            ApiLoginForm::password => User::password,
            ApiLoginForm::device_name => 'test-device',
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
            ApiLoginForm::email => $User->email,
            ApiLoginForm::password => User::password,
            ApiLoginForm::device_name => $deviceName,
        ];

        $this->postJson(api()->login, $payload)->assertOk();
        
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => $deviceName,
            'tokenable_id' => $User->id,
        ]);
    }
}