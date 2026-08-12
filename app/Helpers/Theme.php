<?php

namespace App\Helpers;

enum Theme: string
{
    case light = 'light';
    case dark = 'dark';
    case auto = 'auto';

    public function attribute(): ?string
    {
        return $this === self::auto ? null : $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::light => 'Light',
            self::dark => 'Dark',
            self::auto => 'Auto',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::light => 'Always use the light theme.',
            self::dark => 'Always use the dark theme.',
            self::auto => 'Match the theme your device is set to.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::light, self::auto => '#fafcfe',
            self::dark => '#1b2025',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::light => 'sun',
            self::dark => 'moon',
            self::auto => 'desktop',
        };
    }
}
