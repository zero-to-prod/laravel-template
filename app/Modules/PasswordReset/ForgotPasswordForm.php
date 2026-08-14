<?php

namespace App\Modules\PasswordReset;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\Helpers\SvgName;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class ForgotPasswordForm
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
}
