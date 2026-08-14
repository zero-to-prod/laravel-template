<?php

namespace App\Modules\Settings\Profile;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\Helpers\SvgName;
use App\View\DataModels\TextInput;
use Zerotoprod\DataModel\Describe;

readonly class ProfileForm
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
}
