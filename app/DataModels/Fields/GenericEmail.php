<?php

namespace App\DataModels\Fields;

use App\Helpers\DataModelCast;
use App\Helpers\Rule;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

class GenericEmail
{
    public const int length = 255;
    public const string comment = 'User email address';
    public const array describe = [
        Describe::cast => [DataModelCast::class, 'sanitizeEmail'],
        Field::field => [
            Field::description => self::comment,
            Field::rules => [self::class, 'rules'],
        ],
    ];

    public static function rules(): array
    {
        return [
            Rule::required,
            Rule::string,
            Rule::email,
            Rule::max(self::length),
        ];
    }
}
