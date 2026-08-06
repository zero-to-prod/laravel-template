<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Zerotoprod\DataModelHelper\DataModelHelper;

trait DataModel
{
    use DataModelHelper;
    use \Zerotoprod\DataModel\DataModel;

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $array = json_decode($this->toJson(), true);

        return is_array($array) ? $array : [];
    }

    public function toJson(): string
    {
        return $this->collect()->toJson();
    }

    /** @return Collection<string, mixed> */
    public function collect(): Collection
    {
        return collect(get_object_vars($this));
    }

    /** @return array<int, mixed>|null */
    public function dispatch(): ?array
    {
        return event($this);
    }

    public static function sanitize(?string $value): string
    {
        return Str::squish((string) $value);
    }

    public static function sanitizeEmail(?string $value): string
    {
        return Str::squish(strtolower((string) $value));
    }
}
