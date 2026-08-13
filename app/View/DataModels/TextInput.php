<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use Attribute;
use Zerotoprod\DataModel\Describe;

/**
 * A text field, declared once and read twice.
 *
 * As an attribute on a form property it is that field's presentation, carried
 * beside its type and its rules and never hydrated — the property name is the
 * field name, so a form never repeats it. As a props model it is the same
 * declaration hydrated at render time, when there is a request to read: the value
 * falls back to what was submitted, except for a password, which never repopulates.
 * One class means one set of key constants governs the declaration and its use, and
 * a call site can still layer request-specific data over what was declared.
 *
 * Composes downward by projecting its children's props rather than restating their
 * keys, and owns the decisions the caller should not make — the icon's size, and
 * the fact that the wrapper needs the error key rather than the field name.
 */
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
