<?php

namespace App\Helpers;

use Illuminate\Support\Str;

final readonly class Initials
{
    public static function from(string $name): string
    {
        $words = array_values(array_filter(explode(' ', Str::squish($name))));

        if ($words === []) {
            return '?';
        }

        $last = count($words) > 1 ? Str::substr(end($words), 0, 1) : '';

        return Str::upper(Str::substr($words[0], 0, 1).$last);
    }
}
