<?php

namespace App\Modules\Login;

use ReflectionException;
use Zerotoprod\Factory\Factory;

class LoginFormFactory
{
    use Factory;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            LoginForm::email => 'john@example.com',
            LoginForm::password => LoginForm::password,
            LoginForm::remember_token => true,
        ];
    }

    /** @throws ReflectionException */
    public function make(): LoginForm
    {
        return LoginForm::from($this->context());
    }
}
