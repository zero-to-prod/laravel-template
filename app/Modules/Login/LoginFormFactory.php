<?php

namespace App\Modules\Login;

use Zerotoprod\Factory\Factory;

class LoginFormFactory
{
    use Factory;

    public function definition(): array
    {
        return [
            LoginForm::email => 'john@example.com',
            LoginForm::password => LoginForm::password,
            LoginForm::remember_token => true,
        ];
    }

    public function make(): LoginForm
    {
        return LoginForm::from($this->context());
    }
}