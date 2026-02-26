<?php

namespace App\Modules\Login;

use App\Helpers\DataModel;
use App\Helpers\HasFieldRules;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class LoginForm
{
    use DataModel;
    use HasFieldRules;

    /** @link $email */
    public const string email = 'email';
    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    #[Field(description: 'User email address', rules: 'required|string|email|max:255')]
    public string $email;

    /** @link $password */
    public const string password = 'password';
    #[Field(description: 'User password', rules: 'required|string|max:255')]
    public string $password;

    /** @link $remember_token */
    public const string remember_token = 'remember_token';
    #[Describe(['default' => false])]
    #[Field(description: 'Remember login session')]
    public bool $remember_token;
}