<?php

namespace App\Modules\Login;

use App\Helpers\DataModel;
use App\Helpers\HasRules;
use App\Helpers\Request;
use Zerotoprod\DataModel\Describe;

readonly class LoginRequest
{
    use DataModel;
    use HasRules;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[Request([Request::rules => 'required|string|email|max:255'])]
    public string $email;

    /** @link $password */
    public const string password = 'password';

    #[Request([Request::rules => 'required|string|max:255'])]
    public string $password;

    public const string remember_token = 'remember_token';

    #[Describe([Describe::default => false])]
    public bool $remember_token;
}
