<?php

namespace App\Modules\Api\Support;

use App\Helpers\DataModel;
use BackedEnum;
use Closure;
use Zerotoprod\DataModel\Describe;

readonly class Field
{
    use DataModel;

    public const string field = 'field';
    public const string description = 'description';

    /** A literal, or a callable returning one, so a description can come from its column comment. */
    #[Describe([
        Describe::default => '',
        Describe::cast => [self::class, 'resolveDescription'],
    ])]
    public string $description;

    public static function resolveDescription(mixed $value): string
    {
        $description = $value instanceof Closure || (is_array($value) && is_callable($value))
            ? $value()
            : $value;

        return is_string($description) ? $description : '';
    }

    public const string rules = 'rules';

    /** @var string|array<array-key, mixed> */
    #[Describe([Describe::default => ''])]
    public string|array $rules;

    /** @return list<string> */
    public function resolvedRules(): array
    {
        $rules = is_callable($this->rules) ? ($this->rules)() : $this->rules;

        if (is_string($rules)) {
            return $rules === '' ? [] : explode('|', $rules);
        }

        $resolved = [];

        foreach (is_array($rules) ? $rules : [] as $rule) {
            if ($rule instanceof BackedEnum) {
                $resolved[] = (string) $rule->value;
            } elseif (is_string($rule)) {
                $resolved[] = $rule;
            }
        }

        return $resolved;
    }

    public const string messages = 'messages';

    /** @var array<string, string> */
    #[Describe([Describe::default => []])]
    public array $messages;

    public const string attributes = 'attributes';

    #[Describe([Describe::default => ''])]
    public string $attributes;

    public const string sensitive = 'sensitive';

    #[Describe([Describe::default => false])]
    public bool $sensitive;

    public const string placeholder = 'placeholder';

    #[Describe([Describe::nullable => true])]
    public ?string $placeholder;

    public const string legend = 'legend';

    #[Describe([Describe::nullable => true])]
    public ?string $legend;
}
