<?php

namespace App\Modules\Register;

use App\Helpers\DataModel;
use App\Helpers\DescribesFields;
use App\Helpers\HasFieldRules;
use App\Helpers\HasTextInput;
use App\Modules\Api\Support\Field;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class RegisterForm implements DescribesFields
{
    use DataModel;
    use HasFieldRules;
    use HasTextInput;

    public const string name = 'name';

    #[Describe([
        Describe::cast => [self::class, 'sanitize'],
        Field::field => [
            Field::description => 'User name',
            Field::rules => 'required|string|max:255',
        ],
    ])]
    #[TextInput([
        TextInput::legend => 'Full Name',
        TextInput::icon => 'user',
        TextInput::placeholder => 'First and Last Name',
    ])]
    public string $name;

    public const string email = 'email';

    #[Describe([
        Describe::cast => [self::class, 'sanitizeEmail'],
        Field::field => [
            Field::description => 'User email address',
            Field::rules => 'required|string|email|max:255|unique:users',
        ],
    ])]
    #[TextInput([
        TextInput::legend => 'Email',
        TextInput::type => 'email',
        TextInput::icon => 'email',
        TextInput::placeholder => 'Email',
    ])]
    public string $email;

    public const string password = 'password';

    #[Describe([
        Field::field => [Field::description => 'User password'],
    ])]
    #[TextInput([
        TextInput::legend => 'Password',
        TextInput::type => 'password',
        TextInput::icon => 'key',
        TextInput::placeholder => 'Password',
        TextInput::autocomplete => 'new-password',
        TextInput::required => true,
    ])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[Describe([
        Field::field => [Field::description => 'Password confirmation'],
    ])]
    #[TextInput([
        TextInput::legend => 'Password Confirmation',
        TextInput::type => 'password',
        TextInput::icon => 'key',
        TextInput::placeholder => 'Password Confirmation',
        TextInput::autocomplete => 'new-password',
    ])]
    public string $password_confirmation;
}
