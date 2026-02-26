<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Zerotoprod\DataModelHelper\DataModelHelper;

trait DataModel
{
    use DataModelHelper;
    use \Zerotoprod\DataModel\DataModel;

    public function toArray(): array
    {
        return json_decode($this->toJson(), true);
    }

    public function toJson(): string
    {
        return $this->collect()->toJson();
    }

    public function dd(): void
    {
        dd($this);
    }

    public function collect(): Collection
    {
        return collect($this);
    }

    public function dispatch()
    {
        return event($this);
    }

    public static function sanitize($value): string
    {
        return Str::squish($value);
    }

    public static function sanitizeEmail($value): string
    {
        return Str::squish(strtolower($value));
    }
}
