<?php

namespace App\Sources\Db\Support;

use ReflectionEnum;
use ZeroToProd\SchemaValidator\Property;

enum ColumnType: string
{
    #[OasType(Property::string)]
    case varchar = 'varchar';
    #[OasType(Property::string)]
    case mediumtext = 'mediumtext';
    #[OasType(Property::integer)]
    case int = 'int';
    #[OasType(Property::string, Property::date_time)]
    case timestamp = 'timestamp';
    #[OasType(Property::string)]
    case char = 'char';

    /** @return array<string, string> */
    public function oas(): array
    {
        $OasType = new ReflectionEnum(self::class)
            ->getCase($this->name)
            ->getAttributes(OasType::class)[0]
            ->newInstance();

        return $OasType->format === null
            ? [Property::type => $OasType->type]
            : [Property::type => $OasType->type, Property::format => $OasType->format];
    }
}
