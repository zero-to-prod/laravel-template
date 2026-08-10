<?php

namespace App\Sources\Db\Support;

use ReflectionEnum;
use ZeroToProd\SchemaValidator\Property;

enum ColumnType: string
{
    #[OasType(Property::string)]
    #[RuleType('string')]
    case varchar = 'varchar';
    #[OasType(Property::string)]
    #[RuleType('string')]
    case mediumtext = 'mediumtext';
    #[OasType(Property::integer)]
    #[RuleType('integer')]
    case int = 'int';
    #[OasType(Property::string, Property::date_time)]
    #[RuleType('date')]
    case timestamp = 'timestamp';
    #[OasType(Property::string)]
    #[RuleType('string')]
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

    public function rule(): string
    {
        return new ReflectionEnum(self::class)
            ->getCase($this->name)
            ->getAttributes(RuleType::class)[0]
            ->newInstance()
            ->rule;
    }
}
