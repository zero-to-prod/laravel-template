<?php

namespace App\Routes;

use App\AppConfig;
use Attribute;
use BackedEnum;
use ReflectionEnumBackedCase;

/**
 * Tags a route the administrative index of links displays.
 *
 * Every registered index is read, so a case is tagged wherever it lives — and one
 * outside them is not this application's routing, so it is not displayed. The order
 * is optional: tagged cases sort by it across every index at once, and the ones
 * giving none follow, in the order their own enum declares them. What is collected
 * is a name and a resolved address rather than the case, so a tagged path cannot
 * carry a parameter — there would be nothing to fill it in with.
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class AdminLink
{
    public const string order = 'order';
    public const string name = 'name';
    public const string url = 'url';

    public function __construct(public ?int $order = null) {}

    /** @return list<array{name: string, url: string}> */
    public static function routes(): array
    {
        /** @var array<int, list<array{name: string, url: string}>> $ordered */
        $ordered = [];

        foreach (AppConfig::routeIndexes() as $enum) {
            foreach (self::links($enum) as $link) {
                $ordered[$link[self::order]][] = [
                    self::name => $link[self::name],
                    self::url => $link[self::url],
                ];
            }
        }

        ksort($ordered);

        return array_merge(...array_values($ordered));
    }

    /**
     * @param  class-string<BackedEnum>  $enum
     * @return list<array{order: int, name: string, url: string}>
     */
    public static function links(string $enum): array
    {
        $links = [];

        foreach ($enum::cases() as $Case) {
            $attributes = new ReflectionEnumBackedCase($enum, $Case->name)->getAttributes(self::class);

            if ($attributes === []) {
                continue;
            }

            $links[] = [
                self::order => $attributes[0]->newInstance()->order ?? PHP_INT_MAX,
                self::name => $Case->name,
                self::url => (string) $Case->value,
            ];
        }

        return $links;
    }
}
