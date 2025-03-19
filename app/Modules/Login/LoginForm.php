<?php

namespace App\Modules\Login;

use App\Helpers\DataModelFromRequest;
use Illuminate\Support\Facades\Validator;
use Zerotoprod\DataModel\Describe;

readonly class LoginForm
{
    use DataModelFromRequest;

    /** @link $email */
    public const email = 'email';
    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    public string $email;

    /** @link $password */
    public const password = 'password';
    public string $password;

    /** @link $remember_token */
    public const remember_token = 'remember_token';
    #[Describe(['default' => false])]
    public bool $remember_token;

    public function validator(): \Illuminate\Validation\Validator
    {
        return Validator::make($this->toArray(), [
            self::email => ['required', 'string', 'email', 'max:255'],
            self::password => ['required', 'string', 'max:255'],
        ]);
    }
}