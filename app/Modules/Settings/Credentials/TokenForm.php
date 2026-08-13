<?php

namespace App\Modules\Settings\Credentials;

use App\Helpers\DataModel;
use App\Helpers\HasTextInput;
use App\View\DataModels\TextInput;

readonly class TokenForm
{
    use DataModel;
    use HasTextInput;

    public const string name = 'name';

    #[TextInput([
        TextInput::legend => 'Token Name',
        TextInput::icon => 'command-line',
        TextInput::placeholder => 'Laptop CLI',
        TextInput::title => 'A label to recognise this token by',
        TextInput::required => true,
    ])]
    public string $name;

    public const string expires_at = 'expires_at';

    #[TextInput([
        TextInput::legend => 'Expires',
        TextInput::type => 'date',
        TextInput::title => 'A future date, or empty for a token that never expires',
    ])]
    public ?string $expires_at;
}
