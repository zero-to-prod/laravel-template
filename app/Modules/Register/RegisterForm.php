<?php

namespace App\Modules\Register;

use App\Helpers\DataModelFromRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Zerotoprod\DataModel\Describe;

readonly class RegisterForm
{
    use DataModelFromRequest;

    /** @link $name */
    public const name = 'name';
    #[Describe(['cast' => [self::class, 'sanitize']])]
    public string $name;

    /** @link $email */
    public const email = 'email';
    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    public string $email;

    /** @link $password */
    public const password = 'password';
    public string $password;

    /** @link $password_confirmation */
    public const password_confirmation = 'password_confirmation';
    public string $password_confirmation;

    public function validator(): \Illuminate\Validation\Validator
    {
        return Validator::make($this->toArray(), [
            self::name => ['required', 'string', 'max:255'],
            self::email => ['required', 'string', 'email', 'max:255', 'unique:users'],
            self::password => ['required', 'confirmed', Password::defaults()],
        ]);
    }
}