<?php

namespace App\Modules\Api\Support;

use Illuminate\Support\Carbon;

/**
 * The value, when present, is an instant that has not passed.
 *
 * `format: date-time` publishes what a well-formed instant looks like; it says
 * nothing about which side of now it falls on, so the check lives here rather
 * than in the document.
 */
readonly class Future implements ValueCheck
{
    public function __construct(private ?string $message = null) {}

    /** @param  array<string, mixed>  $data */
    public function violation(mixed $value, string $path, array $data): ?Violation
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value)->isFuture()
            ? null
            : new Violation($path, 'after', $this->message ?? 'The '.$path.' field must be a future date.');
    }
}
