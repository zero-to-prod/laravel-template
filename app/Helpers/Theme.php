<?php

namespace App\Helpers;

/**
 * The appearance a user can choose and keep, and everything each choice implies.
 *
 * The case that defers to the device renders no theme attribute at all: leaving it
 * off is what hands the decision to the operating system, so that case must never
 * be given a name the stylesheet has not registered — and an unregistered name
 * renders an unstyled page. Presentation is answered by exhaustive matching, so a
 * new case fails loudly until every question is answered for it: its label, its
 * description, the icon it shows, and the browser-chrome color that has to stay
 * paired with the background the stylesheet paints. Values are stored, so one has
 * to fit the column. The choices are enumerated when rendered, so the form follows.
 */
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
