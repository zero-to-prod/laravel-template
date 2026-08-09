<?php

namespace App\Modules\Login;

use App\Helpers\DataModel;
use App\Helpers\DescribesFields;
use App\Helpers\HasFieldRules;
use App\Helpers\HasTextInput;
use App\Modules\Api\Support\Field;
use App\Sources\Db\App\Users;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class LoginForm implements DescribesFields
{
    use DataModel;
    use HasFieldRules;
    use HasTextInput;

    /** @link $email */
    public const string email = 'email';

    #[Describe([
        'source' => Users::email,
        Describe::cast => [self::class, 'sanitizeEmail'],
        Field::field => [
            Field::description => 'User email address',
            Field::rules => 'required|string|email|max:255',
        ],
    ])]
    #[TextInput([
        TextInput::legend => 'Email',
        TextInput::icon => 'email',
        TextInput::placeholder => 'Email',
    ])]
    public string $email;

    /** @link $password */
    public const string password = 'password';

    #[Describe([
        Field::field => [
            Field::description => 'User password',
            Field::rules => 'required|string|max:255',
        ],
    ])]
    #[TextInput([
        TextInput::legend => 'Password',
        TextInput::type => 'password',
        TextInput::icon => 'key',
        TextInput::placeholder => 'Password',
        TextInput::autocomplete => 'current-password',
    ])]
    public string $password;

    /** @link $remember_token */
    public const string remember_token = 'remember_token';

    #[Describe([
        Describe::default => false,
        Field::field => [
            Field::description => 'Remember login session',
        ],
    ])]
    public bool $remember_token;
}
