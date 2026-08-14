<?php

namespace App\Modules\Login;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\Helpers\SvgName;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class LoginForm
{
    use DataModel;
    use HasTextInput;

    public const string email = 'email';

    #[TextInput([
        TextInput::legend => 'Email',
        TextInput::type => 'email',
        TextInput::icon => SvgName::email,
        TextInput::placeholder => 'Email',
        TextInput::title => 'User email address',
        TextInput::required => true,
    ])]
    public string $email;

    public const string password = 'password';

    #[TextInput([
        TextInput::legend => 'Password',
        TextInput::type => 'password',
        TextInput::icon => SvgName::key,
        TextInput::placeholder => 'Password',
        TextInput::autocomplete => 'current-password',
        TextInput::title => 'User password',
        TextInput::required => true,
    ])]
    public string $password;

    public const string remember_token = 'remember_token';

    #[Describe([Describe::default => false])]
    public bool $remember_token;
}
