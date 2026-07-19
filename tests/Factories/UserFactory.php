<?php

namespace Tests\Factories;

use App\DataModels\User;
use Zerotoprod\Factory\Factory;

class UserFactory
{
    use Factory;

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

    public function make(): User
    {
        return User::from($this->context());
    }
}
