<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            User::name => fake()->name(),
            User::email => fake()->unique()->safeEmail(),
            User::email_verified_at => now(),
            User::password => static::$password ??= Hash::make('password'),
            User::remember_token => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state([User::email_verified_at => null]);
    }
}
