<?php

namespace App\Modules\Admin\Users\Update;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
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
        TextInput::icon => 'user',
        TextInput::placeholder => 'First and Last Name',
        TextInput::title => 'User name',
        TextInput::required => true,
    ])]
    public string $name;

    public const string email = 'email';

    #[Describe([Describe::cast => [self::class, 'sanitizeEmail']])]
    #[TextInput([
        TextInput::legend => 'Email',
        TextInput::icon => 'email',
        TextInput::placeholder => 'Email',
        TextInput::title => 'User email address',
        TextInput::required => true,
    ])]
    public string $email;
}
