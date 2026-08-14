<?php

namespace App\Modules\PasswordConfirmation;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\Helpers\SvgName;
use App\View\DataModels\TextInput;

readonly class PasswordConfirmationForm
{
    use DataModel;
    use HasTextInput;

    public const string password = 'password';

    #[TextInput([
        TextInput::legend => 'Password',
        TextInput::type => 'password',
        TextInput::icon => SvgName::key,
        TextInput::placeholder => 'Password',
        TextInput::autocomplete => 'current-password',
        TextInput::title => 'Your current password',
        TextInput::required => true,
    ])]
    public string $password;
}
