<?php

namespace App\DataModels\Fields;

use App\Helpers\DataModelCast;
use App\Helpers\Rule;
use App\Modules\Api\Support\Field;
use Zerotoprod\DataModel\Describe;

class GenericString
{
    public const int length = 255;
    public const string comment = 'A free-text value';
    public const array describe = [
        Describe::cast => [DataModelCast::class, 'sanitize'],
        Field::field => [Field::description => self::comment],
    ];

    /** @return list<Rule|string> */
    public static function rules(): array
    {
        return [
            Rule::required,
            Rule::string,
            Rule::max(self::length),
        ];
    }
}
