<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use Attribute;
use Zerotoprod\DataModel\Describe;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TextInput
{
    use DataModel;

    /** @param  array<string, mixed>  $attributes */
    public function __construct(public array $attributes = []) {}

    public const string name = 'name';

    #[Describe([Describe::required => true])]
    public string $name;

    public const string legend = 'legend';

    public ?string $legend = null;

    public const string type = 'type';

    public string $type = 'text';

    public const string value = 'value';

    #[Describe([Describe::default => [self::class, 'oldValue']])]
    public mixed $value;

    public const string icon = 'icon';

    public ?string $icon = null;

    public const string error = 'error';

    #[Describe([Describe::default => [self::class, 'errorKey']])]
    public string $error;

    public const string bag = 'bag';

    public string $bag = 'default';

    public const string placeholder = 'placeholder';

    public ?string $placeholder = null;

    public const string autocomplete = 'autocomplete';

    public ?string $autocomplete = null;

    public const string required = 'required';

    public bool $required = false;

    public const string title = 'title';

    public ?string $title = null;

    public const string configured = 'configured';

    public bool $configured = false;

    public const string configuredLabel = 'configuredLabel';

    public string $configuredLabel = 'value';

    /** @return array<string, mixed> */
    public function fieldset(): array
    {
        return [
            Fieldset::legend => $this->legend,
            Fieldset::name => $this->error,
            Fieldset::bag => $this->bag,
            Fieldset::required => $this->required,
            Fieldset::title => $this->title,
        ];
    }

    /** @return array<string, mixed> */
    public function svg(): array
    {
        return [
            Svg::name => $this->icon,
            Svg::classname => 'h-4 w-4 opacity-70',
        ];
    }

    /** @param  array<string, mixed>  $context */
    public static function oldValue(mixed $value, array $context): mixed
    {
        return ($context[self::type] ?? 'text') === 'password'
            ? null
            : old(self::errorKey($value, $context));
    }

    /** @param  array<string, mixed>  $context */
    public static function errorKey(mixed $value, array $context): string
    {
        $name = $context[self::name] ?? null;

        return is_string($name) ? $name : '';
    }
}
