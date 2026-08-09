<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use Zerotoprod\DataModel\Describe;

class StatusToast
{
    use DataModel;

    public const string sessionKey = 'sessionKey';

    public string $sessionKey = 'status';

    public const string message = 'message';

    #[Describe([Describe::default => [self::class, 'flashedMessage']])]
    public ?string $message;

    public const string alert = 'alert';

    public string $alert = 'alert-success';

    /** @param  array<string, mixed>  $context */
    public static function flashedMessage(mixed $value, array $context): ?string
    {
        $sessionKey = $context[self::sessionKey] ?? 'status';
        $message = is_string($sessionKey) ? session($sessionKey) : null;

        return is_string($message) ? $message : null;
    }
}
