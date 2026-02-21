<?php

namespace App\Modules\Register;

use App\Helpers\DataModel;
use App\Helpers\HasFieldRules;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

readonly class RegisterForm
{
    use DataModel;
    use HasFieldRules;

    /** @link $name */
    public const name = 'name';

    #[Describe(['cast' => [self::class, 'sanitize']])]
    #[Field(description: 'User name', rules: 'required|string|max:255')]
    public string $name;

    /** @link $email */
    public const email = 'email';

    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    #[Field(description: 'User email address', rules: 'required|string|email|max:255|unique:users')]
    public string $email;

    /** @link $password */
    public const password = 'password';

    #[Field(description: 'User password')]
    public string $password;

    /** @link $password_confirmation */
    public const password_confirmation = 'password_confirmation';

    #[Field(description: 'Password confirmation')]
    public string $password_confirmation;
}