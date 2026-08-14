<?php

namespace App\Modules\PasswordReset;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\Helpers\SvgName;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class ResetPasswordForm
{
    use DataModel;
    use HasTextInput;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[TextInput([
        TextInput::legend => 'Email',
        TextInput::type => 'email',
        TextInput::icon => SvgName::email,
        TextInput::placeholder => 'Email',
        TextInput::autocomplete => 'email',
        TextInput::title => 'Your account email address',
        TextInput::required => true,
    ])]
    public string $email;

    public const string password = 'password';

    #[TextInput([
        TextInput::legend => 'New Password',
        TextInput::type => 'password',
        TextInput::icon => SvgName::key,
        TextInput::placeholder => 'New Password',
        TextInput::autocomplete => 'new-password',
        TextInput::title => 'Your new password',
        TextInput::required => true,
    ])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[TextInput([
        TextInput::legend => 'Confirm New Password',
        TextInput::type => 'password',
        TextInput::icon => SvgName::key,
        TextInput::placeholder => 'Confirm New Password',
        TextInput::autocomplete => 'new-password',
        TextInput::title => 'Confirm your new password',
        TextInput::required => true,
    ])]
    public string $password_confirmation;

    public const string token = 'token';

    #[Describe([Describe::default => ''])]
    public string $token;
}
