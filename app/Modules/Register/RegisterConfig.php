<?php

namespace App\Modules\Register;

class RegisterConfig
{
    public function rateLimitKey(?string $key = null): string
    {
        return 'register:'.$key;
    }

    public function rateLimitMaxAttempts(): int
    {
        return 5;
    }

    public function tooManyAttemptsMessage(): string
    {
        return 'Too many registration attempts. Please try again later.';
    }
}