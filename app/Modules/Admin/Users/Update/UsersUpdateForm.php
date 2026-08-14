<?php

namespace App\Modules\Admin\Users\Update;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\Helpers\SvgName;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class UsersUpdateForm
{
    use DataModel;
    use HasTextInput;

    public const string name = 'name';

    #[Describe([Describe::cast => [self::class, 'sanitize']])]
    #[TextInput([
        TextInput::legend => 'Full Name',
        TextInput::icon => SvgName::user,
        TextInput::placeholder => 'First and Last Name',
        TextInput::title => 'User name',
        TextInput::required => true,
    ])]
    public string $name;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[TextInput([
        TextInput::legend => 'Email',
        TextInput::icon => SvgName::email,
        TextInput::placeholder => 'Email',
        TextInput::title => 'User email address',
        TextInput::required => true,
    ])]
    public string $email;

    public const string password = 'password';

    #[TextInput([
        TextInput::legend => 'New Password',
        TextInput::type => 'password',
        TextInput::icon => SvgName::key,
        TextInput::placeholder => 'Leave blank to keep the current password',
        TextInput::autocomplete => 'new-password',
        TextInput::title => 'Replace the user password',
    ])]
    public string $password;

    public const string password_confirmation = 'password_confirmation';

    #[TextInput([
        TextInput::legend => 'Confirm New Password',
        TextInput::type => 'password',
        TextInput::icon => SvgName::key,
        TextInput::placeholder => 'Confirm the new password',
        TextInput::autocomplete => 'new-password',
        TextInput::title => 'New password confirmation',
    ])]
    public string $password_confirmation;
}
