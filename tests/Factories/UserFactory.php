<?php

namespace Tests\Factories;

use App\DataModels\User;
use ReflectionException;
use Zerotoprod\Factory\Factory;

class UserFactory
{
    use Factory;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            User::name => 'name',
            User::email => 'john@example.com',
            User::password => 'password',
            User::password_confirmation => 'password',
            User::remember_token => true,
        ];
    }

    /** @throws ReflectionException */
    public function make(): User
    {
        return User::from($this->context());
    }
}
