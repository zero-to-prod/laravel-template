<?php

namespace App\Modules\Register;

use ReflectionException;
use Zerotoprod\Factory\Factory;

class RegisterFormFactory
{
    use Factory;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            RegisterForm::name => RegisterForm::name,
            RegisterForm::email => 'john@example.com',
            RegisterForm::password => RegisterForm::password,
            RegisterForm::password_confirmation => RegisterForm::password,
        ];
    }

    /** @throws ReflectionException */
    public function make(): RegisterForm
    {
        return RegisterForm::from($this->context());
    }
}
