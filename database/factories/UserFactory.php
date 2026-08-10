<?php

namespace Database\Factories;

use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            Users::name->value => fake()->name(),
            Users::email->value => fake()->unique()->safeEmail(),
            Users::email_verified_at->value => now(),
            Users::password->value => static::$password ??= Hash::make('password'),
            Users::remember_token->value => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state([Users::email_verified_at->value => null]);
    }
}
