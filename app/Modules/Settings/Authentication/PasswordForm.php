<?php

namespace App\Modules\Settings\Authentication;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\View\DataModels\TextInput;

readonly class PasswordForm
{
    use DataModel;
    use HasTextInput;

    public const string current_password = 'current_password';

    #[TextInput([
        TextInput::legend => 'Current Password',
        TextInput::type => 'password',
        TextInput::icon => 'key',
        TextInput::placeholder => 'Current Password',
        TextInput::autocomplete => 'current-password',
        TextInput::title => 'The password you sign in with today',
        TextInput::required => true,
    ])]
    public string $current_password;

    public const string password = 'password';

    #[TextInput([
        TextInput::legend => 'New Password',
        TextInput::type => 'password',
        TextInput::icon => 'key',
        TextInput::placeholder => 'New Password',
        TextInput::autocomplete => 'new-password',
        TextInput::title => 'The password you want to use',
        TextInput::required => true,
    ])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[TextInput([
        TextInput::legend => 'Confirm New Password',
        TextInput::type => 'password',
        TextInput::icon => 'key',
        TextInput::placeholder => 'Confirm New Password',
        TextInput::autocomplete => 'new-password',
        TextInput::title => 'New password confirmation',
        TextInput::required => true,
    ])]
    public string $password_confirmation;
}
